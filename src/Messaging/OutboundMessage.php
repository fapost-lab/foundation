<?php

declare(strict_types=1);

namespace FAPost\Foundation\Messaging;

use Spatie\LaravelData\Data;

/**
 * Fully prepared outbound message envelope ready for queue dispatch and delivery.
 */
final class OutboundMessage extends Data
{
    /**
     * @param  string  $idempotencyKey  Engine-generated key used for deduplication.
     * @param  string  $tenantId  Tenant that owns the delivery.
     * @param  string  $channelId  Configured channel identifier inside the tenant.
     * @param  string  $channelType  Published channel integration key such as telegram.
     * @param  string|null  $transportToken  Transport-level credential resolved before queuing.
     * @param  string  $chatId  External recipient or chat identifier.
     * @param  MessagePayload  $payload  Normalized outbound content payload.
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly string $tenantId,
        public readonly string $channelId,
        public readonly string $channelType,
        public readonly ?string $transportToken,
        public readonly string $chatId,
        public readonly MessagePayload $payload,
        public readonly array $metadata = [],
    ) {
    }
}
