<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

use ValueError;

/**
 * Raw ingress payload dispatched from the webhook ingress controller to the worker.
 *
 * Contains everything needed to process the incoming message inside the tenant-aware worker,
 * without any normalization or DB access on the ingress side.
 * Lives in Foundation because external channel adapters (Viber, Instagram DM via plugins)
 * both produce and consume this contract.
 *
 * {@see self::fromArray()} and {@see self::toArray()} define the queue wire format,
 * which an external ingress gateway writes directly. That makes the shape here a
 * cross-language contract: fields may be added, but existing ones may not change
 * meaning or type without a version bump and a coordinated release of both sides.
 */
final readonly class InboundWebhookPayload
{
    /** Wire format version. Bump only on a breaking change to the field set. */
    public const int VERSION = 1;

    /**
     * @param  array<string, mixed>  $rawPayload   Full decoded webhook body, as-is from the provider.
     */
    public function __construct(
        /** Tenant identifier from the webhook registry. */
        public string $tenantId,

        /** Database schema name for tenant context switch. */
        public string $schema,

        /** Assistant that owns this channel. */
        public string $assistantId,

        /** Channel identifier from the webhook registry. */
        public string $channelId,

        /** Source platform (telegram, whatsapp, …). */
        public string $platform,

        /** Full decoded webhook body, as-is from the provider. */
        public array $rawPayload,

        /**
         * Idempotency key already written to Redis by ingress (audit only).
         * Format is per-platform — see ChannelAdapterInterface::extractIdempotencyKey().
         */
        public string $idempotencyKey,

        /** Unix timestamp when the ingress controller received the request. */
        public int $receivedAt,

        /**
         * Correlation id assigned at ingress, carried through to the worker logs.
         *
         * Optional: the id originates at whichever runtime accepted the request, and
         * a payload built before this field existed simply has none.
         */
        public ?string $requestId = null,
    ) {
    }

    /**
     * Rebuild the payload from its queue wire representation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValueError When the payload version is not recognized.
     */
    public static function fromArray(array $data): self
    {
        $version = (int) ($data['v'] ?? 0);

        // Refusing an unknown version is deliberate: a payload written by a newer
        // gateway may carry fields this worker would silently ignore, and a webhook
        // processed with half its context is worse than one that fails loudly.
        if (self::VERSION !== $version) {
            throw new ValueError("Unsupported inbound webhook payload version: {$version}.");
        }

        $requestId = $data['requestId'] ?? null;

        return new self(
            tenantId: (string) ($data['tenantId'] ?? ''),
            schema: (string) ($data['schema'] ?? ''),
            assistantId: (string) ($data['assistantId'] ?? ''),
            channelId: (string) ($data['channelId'] ?? ''),
            platform: (string) ($data['platform'] ?? ''),
            rawPayload: (array) ($data['rawPayload'] ?? []),
            idempotencyKey: (string) ($data['idempotencyKey'] ?? ''),
            receivedAt: (int) ($data['receivedAt'] ?? 0),
            requestId: null === $requestId ? null : (string) $requestId,
        );
    }

    /**
     * Queue wire representation, shared with every ingress runtime.
     *
     * @return array{
     *     v: int,
     *     tenantId: string,
     *     schema: string,
     *     assistantId: string,
     *     channelId: string,
     *     platform: string,
     *     rawPayload: array<string, mixed>,
     *     idempotencyKey: string,
     *     receivedAt: int,
     *     requestId: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'v'              => self::VERSION,
            'tenantId'       => $this->tenantId,
            'schema'         => $this->schema,
            'assistantId'    => $this->assistantId,
            'channelId'      => $this->channelId,
            'platform'       => $this->platform,
            'rawPayload'     => $this->rawPayload,
            'idempotencyKey' => $this->idempotencyKey,
            'receivedAt'     => $this->receivedAt,
            'requestId'      => $this->requestId,
        ];
    }
}
