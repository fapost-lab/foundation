<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Raw ingress payload dispatched from the webhook ingress controller to the worker.
 *
 * Contains everything needed to process the incoming message inside the tenant-aware worker,
 * without any normalization or DB access on the ingress side.
 * Lives in Foundation because external channel adapters (Viber, Instagram DM via plugins)
 * both produce and consume this contract.
 */
final readonly class InboundWebhookPayload
{
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
    ) {
    }
}
