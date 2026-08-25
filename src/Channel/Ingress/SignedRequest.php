<?php

declare(strict_types=1);

namespace FAPost\Foundation\Channel\Ingress;

use JsonException;

/**
 * Transport-agnostic view of an inbound webhook request, carrying exactly the
 * data an {@see IngressSpec} needs to verify a signature and derive an
 * idempotency key.
 *
 * Deliberately free of any framework request object so the same spec execution
 * runs inside the PHP controller and can be mirrored by an external gateway.
 *
 * The raw body is kept as the original byte string: HMAC schemes sign the bytes
 * the provider sent, and a decode/re-encode round trip would invalidate them.
 */
final class SignedRequest
{
    /** @var array<string, mixed>|null Lazily decoded body, cached across placeholder lookups. */
    private ?array $decodedBody = null;

    private bool $decodeAttempted = false;

    /**
     * @param  string                 $rawBody  Raw request body exactly as received.
     * @param  array<string, string>  $headers  Header names are normalized to lower case by {@see self::create()}.
     * @param  array<string, string>  $query    Query string parameters.
     */
    public function __construct(
        public readonly string $rawBody,
        public readonly array $headers = [],
        public readonly array $query = [],
    ) {
    }

    /**
     * Build a request with header names normalized for case-insensitive lookup.
     *
     * @param  array<string, string|array<int, string>>  $headers
     * @param  array<string, string>                     $query
     */
    public static function create(string $rawBody, array $headers = [], array $query = []): self
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[mb_strtolower($name)] = is_array($value) ? (string) ($value[0] ?? '') : $value;
        }

        return new self($rawBody, $normalized, $query);
    }

    /**
     * Header value, or an empty string when absent. Lookup is case-insensitive.
     */
    public function header(string $name): string
    {
        return $this->headers[mb_strtolower($name)] ?? '';
    }

    /**
     * Query parameter value, or an empty string when absent.
     */
    public function queryParam(string $name): string
    {
        return $this->query[$name] ?? '';
    }

    /**
     * Decoded JSON body, or an empty array when the body is absent or not valid JSON.
     *
     * Never throws: ingress must answer the provider even for malformed payloads,
     * and a body that cannot be decoded simply yields no placeholder values.
     *
     * @return array<string, mixed>
     */
    public function body(): array
    {
        if ($this->decodeAttempted) {
            return $this->decodedBody ?? [];
        }

        $this->decodeAttempted = true;

        try {
            $decoded = json_decode($this->rawBody, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->decodedBody = [];
        }

        return $this->decodedBody = is_array($decoded) ? $decoded : [];
    }
}
