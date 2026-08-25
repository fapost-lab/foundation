<?php

declare(strict_types=1);

namespace FAPost\Foundation\Channel\Ingress;

use JsonSerializable;
use ValueError;

/**
 * Declarative description of how a platform's inbound webhook is authenticated
 * and deduplicated, expressed as data rather than code.
 *
 * The point of the indirection is that ingress can run outside PHP. A gateway
 * that never heard of Telegram can still verify a Telegram webhook by executing
 * this spec, which means adding a channel — including one shipped by a plugin —
 * stays a pure PHP task and requires no gateway release.
 *
 * Specs are published to Redis per platform and consumed by every ingress
 * runtime. Adapters opt in by implementing {@see ProvidesIngressSpecInterface};
 * anything too exotic to express declaratively simply does not implement it,
 * and ingress falls back to the adapter's own verification code.
 */
final readonly class IngressSpec implements JsonSerializable
{
    /** Wire format version. Bump only on a breaking change to the payload shape. */
    public const int VERSION = 1;

    /**
     * @param  string|null  $parameter            Header or query parameter carrying the candidate signature.
     * @param  string       $prefix               Literal prefix the provider prepends to the signature (e.g. "sha256=").
     * @param  string       $idempotencyTemplate  Template resolved by {@see IngressSpecExecutor::idempotencyKey()}.
     */
    public function __construct(
        public SignatureScheme $scheme,
        public ?string $parameter,
        public string $prefix,
        public string $idempotencyTemplate,
    ) {
        if ((null === $parameter || '' === $parameter) && $scheme->requiresParameter()) {
            throw new ValueError("Signature scheme {$scheme->value} requires a parameter name.");
        }
    }

    /**
     * Constant-time comparison of a header against the channel secret (Telegram).
     */
    public static function headerEquals(string $header, string $idempotencyTemplate): self
    {
        return new self(SignatureScheme::HeaderEquals, $header, '', $idempotencyTemplate);
    }

    /**
     * HMAC-SHA256 over the raw body, compared against a header (Meta/WhatsApp).
     */
    public static function hmacSha256(string $header, string $idempotencyTemplate, string $prefix = ''): self
    {
        return new self(SignatureScheme::HmacSha256, $header, $prefix, $idempotencyTemplate);
    }

    /**
     * HMAC-SHA1 over the raw body, compared against a header (legacy providers).
     */
    public static function hmacSha1(string $header, string $idempotencyTemplate, string $prefix = ''): self
    {
        return new self(SignatureScheme::HmacSha1, $header, $prefix, $idempotencyTemplate);
    }

    /**
     * Constant-time comparison of a query parameter against the channel secret.
     */
    public static function queryParam(string $name, string $idempotencyTemplate): self
    {
        return new self(SignatureScheme::QueryParam, $name, '', $idempotencyTemplate);
    }

    /**
     * No signature verification — the unguessable public hash is the only credential.
     */
    public static function none(string $idempotencyTemplate): self
    {
        return new self(SignatureScheme::None, null, '', $idempotencyTemplate);
    }

    /**
     * Rebuild a spec from its wire representation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValueError When the payload version or scheme is not recognized.
     */
    public static function fromArray(array $data): self
    {
        $version = (int) ($data['v'] ?? 0);

        if (self::VERSION !== $version) {
            throw new ValueError("Unsupported ingress spec version: {$version}.");
        }

        $parameter = $data['parameter'] ?? null;

        return new self(
            scheme: SignatureScheme::from((string) ($data['scheme'] ?? '')),
            parameter: null === $parameter ? null : (string) $parameter,
            prefix: (string) ($data['prefix'] ?? ''),
            idempotencyTemplate: (string) ($data['idempotency'] ?? ''),
        );
    }

    /**
     * Wire representation shared with every non-PHP ingress runtime.
     *
     * @return array{v: int, scheme: string, parameter: string|null, prefix: string, idempotency: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'v'           => self::VERSION,
            'scheme'      => $this->scheme->value,
            'parameter'   => $this->parameter,
            'prefix'      => $this->prefix,
            'idempotency' => $this->idempotencyTemplate,
        ];
    }
}
