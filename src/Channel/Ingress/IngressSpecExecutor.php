<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Ingress;

/**
 * Executes an {@see IngressSpec} against an inbound request.
 *
 * This is the reference implementation of the spec semantics. Any other ingress
 * runtime must reproduce it exactly — the golden fixtures in the test suite exist
 * to catch drift between implementations.
 */
final readonly class IngressSpecExecutor
{
    /** Placeholder resolving to the channel identifier from the webhook registry. */
    private const string CHANNEL_PLACEHOLDER = 'channel';

    /** Placeholder prefix for dot-path lookups into the decoded JSON body. */
    private const string BODY_PREFIX = 'body.';

    /** Placeholder prefix for header lookups. */
    private const string HEADER_PREFIX = 'header.';

    /** Placeholder resolving to the raw request body; allowed only in the signed template. */
    private const string RAW_PLACEHOLDER = 'raw';

    /**
     * Verify the request signature according to the spec.
     *
     * All comparisons are constant-time: a timing oracle here would leak the
     * channel secret to anyone who knows the public hash.
     *
     * An empty secret refuses every scheme that reads one: compared directly it
     * matches an absent header, and as an HMAC key it lets anyone sign.
     *
     * @param  int|null  $now  unix seconds the timestamp check compares against; null means the current time
     */
    public function verify(IngressSpec $spec, SignedRequest $request, string $secret, ?int $now = null): bool
    {
        if (SignatureScheme::None !== $spec->scheme && '' === $secret) {
            return false;
        }

        if (null !== $spec->timestamp && !$this->timestampIsFresh($spec->timestamp, $request, $now ?? time())) {
            return false;
        }

        return match ($spec->scheme) {
            SignatureScheme::None         => true,
            SignatureScheme::HeaderEquals => hash_equals(
                $secret,
                $request->header((string)$spec->parameter),
            ),
            SignatureScheme::QueryParam => hash_equals(
                $secret,
                $request->queryParam((string)$spec->parameter),
            ),
            SignatureScheme::HmacSha256, SignatureScheme::HmacSha1 => $this->verifyHmac($spec, $request, $secret),
        };
    }

    /**
     * Resolve the spec's idempotency template into a concrete deduplication key.
     *
     * Unresolved placeholders collapse to an empty string, mirroring the behaviour
     * of the hand-written adapters this replaces.
     */
    public function idempotencyKey(IngressSpec $spec, SignedRequest $request, string $channelId): string
    {
        return $this->resolve($spec->idempotencyTemplate, $request, $channelId, $this->document($spec, $request), false);
    }

    /**
     * The request document: the JSON body, or the JSON held in the spec's form field, or an empty array.
     * Content-Type is never consulted.
     *
     * @return array<int|string, mixed>
     *
     * @experimental Since foundation 0.33.
     */
    public function document(IngressSpec $spec, SignedRequest $request): array
    {
        $json = json_decode($request->rawBody, true);

        if (is_array($json)) {
            return $json;
        }

        if (null === $spec->formField) {
            return [];
        }

        $value = $this->formValue($request->rawBody, $spec->formField);

        if (null === $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * The plain-text answer to a provider's challenge, or null when the request is not one.
     * Call it only after {@see verify()} returned true.
     *
     * @experimental Since foundation 0.33.
     */
    public function handshakeResponse(IngressSpec $spec, SignedRequest $request): ?string
    {
        if (null === $spec->handshake) {
            return null;
        }

        $document = $this->document($spec, $request);

        if ($this->resolve($spec->handshake->when, $request, '', $document, false) !== $spec->handshake->equals) {
            return null;
        }

        return $this->resolve($spec->handshake->respond, $request, '', $document, false);
    }

    private function timestampIsFresh(IngressTimestamp $timestamp, SignedRequest $request, int $now): bool
    {
        $value = $request->header($timestamp->header);

        if (1 !== preg_match('/^[0-9]{1,19}$/D', $value)) {
            return false;
        }

        $digits = mb_ltrim($value, '0');
        $digits = '' === $digits ? '0' : $digits;

        if (mb_strlen($digits) > 19 || (19 === mb_strlen($digits) && strcmp($digits, (string)PHP_INT_MAX) > 0)) {
            return false;
        }

        return abs($now - (int)$digits) <= $timestamp->toleranceSeconds;
    }

    /**
     * Value of the first form field named $field, decoded strictly; null when absent or malformed.
     *
     * Hand-written on purpose: parse_str() and urldecode() are lenient about invalid escapes and treat `.` and
     * `[` in names specially, and the gateway must reach the same answer byte for byte.
     */
    private function formValue(string $rawBody, string $field): ?string
    {
        foreach (explode('&', $rawBody) as $segment) {
            if ('' === $segment) {
                continue;
            }

            // Byte-wise on purpose: the body is not guaranteed to be valid UTF-8.
            [$name, $value] = explode('=', $segment, 2) + [1 => ''];

            if ($this->decodeFormComponent($name) !== $field) {
                continue;
            }

            return $this->decodeFormComponent($value);
        }

        return null;
    }

    private function decodeFormComponent(string $component): ?string
    {
        $component = str_replace('+', ' ', $component);

        if (1 === preg_match('/%(?![0-9A-Fa-f]{2})/', $component)) {
            return null;
        }

        return preg_replace_callback(
            '/%([0-9A-Fa-f]{2})/',
            static fn (array $matches): string => chr((int)hexdec($matches[1])),
            $component,
        );
    }

    private function verifyHmac(IngressSpec $spec, SignedRequest $request, string $secret): bool
    {
        $algorithm = $spec->scheme->hashAlgorithm();

        if (null === $algorithm) {
            return false;
        }

        // Signed over the raw bytes the provider sent — decoding and re-encoding
        // the body would reorder keys and change escaping, breaking the digest.
        $message = null === $spec->signedPayload
            ? $request->rawBody
            : $this->resolve($spec->signedPayload, $request, '', [], true);

        $expected = $spec->prefix . hash_hmac($algorithm, $message, $secret);

        return hash_equals($expected, $request->header((string)$spec->parameter));
    }

    /**
     * @param  array<int|string, mixed>  $document
     */
    private function resolve(string $template, SignedRequest $request, string $channelId, array $document, bool $allowRaw): string
    {
        return (string)preg_replace_callback(
            '/\{([^}]+)\}/',
            function (array $matches) use ($request, $channelId, $document, $allowRaw): string {
                foreach (explode('|', $matches[1]) as $alternative) {
                    $value = $this->resolvePlaceholder($alternative, $request, $channelId, $document, $allowRaw);

                    if ('' !== $value) {
                        return $value;
                    }
                }

                return '';
            },
            $template,
        );
    }

    /**
     * @param  array<int|string, mixed>  $document
     */
    private function resolvePlaceholder(string $placeholder, SignedRequest $request, string $channelId, array $document, bool $allowRaw): string
    {
        if (self::CHANNEL_PLACEHOLDER === $placeholder) {
            return $channelId;
        }

        if (self::RAW_PLACEHOLDER === $placeholder) {
            return $allowRaw ? $request->rawBody : '';
        }

        if (str_starts_with($placeholder, self::HEADER_PREFIX)) {
            return $request->header(mb_substr($placeholder, mb_strlen(self::HEADER_PREFIX)));
        }

        if (str_starts_with($placeholder, self::BODY_PREFIX)) {
            return $this->scalarAtPath(
                $document,
                mb_substr($placeholder, mb_strlen(self::BODY_PREFIX)),
            );
        }

        return '';
    }

    /**
     * Read a dot-separated path out of the document, stringifying scalars only.
     *
     * @param  array<int|string, mixed>  $body
     */
    private function scalarAtPath(array $body, string $path): string
    {
        $cursor = $body;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return '';
            }

            $cursor = $cursor[$segment];
        }

        return match (true) {
            is_string($cursor)                 => $cursor,
            is_int($cursor), is_float($cursor) => (string)$cursor,
            is_bool($cursor)                   => $cursor ? '1' : '0',
            default                            => '',
        };
    }
}
