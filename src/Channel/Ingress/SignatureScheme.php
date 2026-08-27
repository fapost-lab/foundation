<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Ingress;

/**
 * Declarative signature verification schemes supported by the ingress layer.
 *
 * Each case must be implementable by any ingress runtime (PHP controller,
 * external gateway) from the spec data alone, without platform-specific code.
 */
enum SignatureScheme: string
{
    /** No signature verification — the public hash is the only credential. */
    case None = 'none';

    /** Constant-time compare of a header value against the channel secret (Telegram). */
    case HeaderEquals = 'header_equals';

    /** HMAC-SHA256 over the raw request body, compared against a header (Meta/WhatsApp). */
    case HmacSha256 = 'hmac_sha256';

    /** HMAC-SHA1 over the raw request body, compared against a header (legacy providers). */
    case HmacSha1 = 'hmac_sha1';

    /** Constant-time compare of a query string parameter against the channel secret. */
    case QueryParam = 'query_param';

    /**
     * Hash algorithm backing this scheme, or null when the scheme is not HMAC-based.
     */
    public function hashAlgorithm(): ?string
    {
        return match ($this) {
            self::HmacSha256 => 'sha256',
            self::HmacSha1   => 'sha1',
            default          => null,
        };
    }

    /**
     * Whether this scheme reads its candidate value from a named parameter.
     */
    public function requiresParameter(): bool
    {
        return self::None !== $this;
    }
}
