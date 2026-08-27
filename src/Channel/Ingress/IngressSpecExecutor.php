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

    /**
     * Verify the request signature according to the spec.
     *
     * All comparisons are constant-time: a timing oracle here would leak the
     * channel secret to anyone who knows the public hash.
     */
    public function verify(IngressSpec $spec, SignedRequest $request, string $secret): bool
    {
        return match ($spec->scheme) {
            SignatureScheme::None                                  => true,
            SignatureScheme::HeaderEquals                          => hash_equals(
                $secret,
                $request->header((string)$spec->parameter),
            ),
            SignatureScheme::QueryParam                            => hash_equals(
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
        return preg_replace_callback(
            '/\{([^}]+)\}/',
            fn(array $matches): string => $this->resolvePlaceholder($matches[1], $request, $channelId),
            $spec->idempotencyTemplate,
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
        $expected = $spec->prefix . hash_hmac($algorithm, $request->rawBody, $secret);

        return hash_equals($expected, $request->header((string)$spec->parameter));
    }

    private function resolvePlaceholder(string $placeholder, SignedRequest $request, string $channelId): string
    {
        if (self::CHANNEL_PLACEHOLDER === $placeholder) {
            return $channelId;
        }

        if (str_starts_with($placeholder, self::HEADER_PREFIX)) {
            return $request->header(mb_substr($placeholder, mb_strlen(self::HEADER_PREFIX)));
        }

        if (str_starts_with($placeholder, self::BODY_PREFIX)) {
            return $this->scalarAtPath(
                $request->body(),
                mb_substr($placeholder, mb_strlen(self::BODY_PREFIX)),
            );
        }

        return '';
    }

    /**
     * Read a dot-separated path out of the decoded body, stringifying scalars only.
     *
     * @param  array<string, mixed>  $body
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
