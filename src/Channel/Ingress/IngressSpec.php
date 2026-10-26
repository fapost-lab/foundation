<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Ingress;

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
    /** Wire format version of a spec that uses none of the version 2 features. */
    public const int VERSION = 1;

    /**
     * Wire format version of a spec with a signed template, a timestamp, a form field, a handshake or an
     * idempotency alternative. A gateway that predates it rejects the spec and proxies the request to the
     * application.
     *
     * @experimental Since foundation 0.33.
     */
    public const int VERSION_2 = 2;

    /** Raw-body placeholder that must appear exactly once in a signed template. */
    private const string RAW_PLACEHOLDER = '{raw}';

    /**
     * @param  string|null  $parameter            Header or query parameter carrying the candidate signature.
     * @param  string       $prefix               Literal prefix the provider prepends to the signature (e.g. "sha256=").
     * @param  string       $idempotencyTemplate  Template resolved by {@see IngressSpecExecutor::idempotencyKey()}.
     * @param  string|null  $signedPayload        Since 0.33: template of the string the HMAC covers, containing `{raw}`
     *                                            exactly once; null signs the raw body. HMAC schemes only.
     * @param  IngressTimestamp|null  $timestamp  Since 0.33: request-age check. HMAC schemes only.
     * @param  string|null  $formField            Since 0.33: name of the form field whose value is the JSON document
     *                                            when the body is not JSON (Slack interactivity `payload`).
     * @param  IngressHandshake|null  $handshake  Since 0.33: endpoint-ownership challenge answered without enqueueing.
     *
     * @throws ValueError When the combination of values is invalid.
     */
    public function __construct(
        public SignatureScheme $scheme,
        public ?string $parameter,
        public string $prefix,
        public string $idempotencyTemplate,
        public ?string $signedPayload = null,
        public ?IngressTimestamp $timestamp = null,
        public ?string $formField = null,
        public ?IngressHandshake $handshake = null,
    ) {
        if ((null === $parameter || '' === $parameter) && $scheme->requiresParameter()) {
            throw new ValueError("Signature scheme {$scheme->value} requires a parameter name.");
        }

        $isHmac = null !== $scheme->hashAlgorithm();

        if (null !== $signedPayload) {
            if (!$isHmac) {
                throw new ValueError("A signed template needs an HMAC scheme, not {$scheme->value}.");
            }

            if (2 !== count(explode(self::RAW_PLACEHOLDER, $signedPayload, 3))) {
                throw new ValueError('A signed template must contain {raw} exactly once.');
            }
        }

        if (null !== $timestamp && !$isHmac) {
            throw new ValueError("A timestamp check needs an HMAC scheme, not {$scheme->value}.");
        }

        if (null !== $formField && 1 !== preg_match('/^[A-Za-z0-9_.\-]{1,64}$/D', $formField)) {
            throw new ValueError('The form field name must be 1 to 64 characters of letters, digits, "_", "." or "-".');
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
     * @throws ValueError When the payload version, scheme or a version 2 field is not valid.
     */
    public static function fromArray(array $data): self
    {
        $version = (int) ($data['v'] ?? 0);

        if (self::VERSION !== $version && self::VERSION_2 !== $version) {
            throw new ValueError("Unsupported ingress spec version: {$version}.");
        }

        $parameter = $data['parameter'] ?? null;
        $signed    = $data['signed'] ?? null;
        $timestamp = $data['timestamp'] ?? null;
        $form      = $data['form'] ?? null;
        $handshake = $data['handshake'] ?? null;

        if (self::VERSION === $version && (null !== $signed || null !== $timestamp || null !== $form || null !== $handshake)) {
            throw new ValueError('An ingress spec of version 1 cannot carry version 2 fields.');
        }

        if ((null !== $signed && !is_string($signed)) || (null !== $form && !is_string($form))) {
            throw new ValueError('The "signed" and "form" fields of an ingress spec must be strings.');
        }

        return new self(
            scheme: SignatureScheme::from((string) ($data['scheme'] ?? '')),
            parameter: null === $parameter ? null : (string) $parameter,
            prefix: (string) ($data['prefix'] ?? ''),
            idempotencyTemplate: (string) ($data['idempotency'] ?? ''),
            signedPayload: $signed,
            timestamp: null === $timestamp ? null : self::timestampFromArray($timestamp),
            formField: $form,
            handshake: null === $handshake ? null : self::handshakeFromArray($handshake),
        );
    }

    /**
     * 2 when the spec uses any feature added in wire version 2, otherwise 1.
     *
     * @experimental Since foundation 0.33.
     */
    public function wireVersion(): int
    {
        $usesV2 = null !== $this->signedPayload
            || null !== $this->timestamp
            || null !== $this->formField
            || null !== $this->handshake
            || 1 === preg_match('/\{[^}]*\|[^}]*\}/', $this->idempotencyTemplate);

        return $usesV2 ? self::VERSION_2 : self::VERSION;
    }

    /**
     * Wire representation shared with every non-PHP ingress runtime.
     *
     * A version 1 spec serialises exactly as it always did; a version 2 spec always carries all four new keys.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $wire = [
            'v'           => $this->wireVersion(),
            'scheme'      => $this->scheme->value,
            'parameter'   => $this->parameter,
            'prefix'      => $this->prefix,
            'idempotency' => $this->idempotencyTemplate,
        ];

        if (self::VERSION === $wire['v']) {
            return $wire;
        }

        return $wire + [
            'signed'    => $this->signedPayload,
            'timestamp' => $this->timestamp?->jsonSerialize(),
            'form'      => $this->formField,
            'handshake' => $this->handshake?->jsonSerialize(),
        ];
    }

    private static function timestampFromArray(mixed $data): IngressTimestamp
    {
        if (!is_array($data) || !is_string($data['header'] ?? null) || !is_int($data['tolerance'] ?? null)) {
            throw new ValueError('The "timestamp" field of an ingress spec needs a string header and an integer tolerance.');
        }

        return new IngressTimestamp($data['header'], $data['tolerance']);
    }

    private static function handshakeFromArray(mixed $data): IngressHandshake
    {
        if (!is_array($data) || !is_string($data['when'] ?? null) || !is_string($data['equals'] ?? null) || !is_string($data['respond'] ?? null)) {
            throw new ValueError('The "handshake" field of an ingress spec needs string "when", "equals" and "respond" values.');
        }

        return new IngressHandshake($data['when'], $data['equals'], $data['respond']);
    }
}
