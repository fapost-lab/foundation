<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use Spatie\LaravelData\Data;

/**
 * Fully prepared outbound message envelope ready for queue dispatch and delivery.
 *
 * The envelope carries no secret, so it may be queued as it is: it names the channel by
 * {@see $channelId}, and the channel's credentials reach the provider sender only when the message is
 * sent ({@see ProviderSenderInterface::deliver()}). Since foundation 0.31 it has no transport token.
 */
final class OutboundMessage extends Data
{
    /**
     * @param  string  $idempotencyKey  Engine-generated key used for deduplication.
     * @param  string  $tenantId  Tenant that owns the delivery.
     * @param  string  $channelId  Configured channel identifier inside the tenant.
     * @param  string  $channelType  Published channel integration key such as telegram.
     * @param  string  $chatId  External recipient or chat identifier.
     * @param  MessagePayload  $payload  Normalized outbound content payload.
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly string $tenantId,
        public readonly string $channelId,
        public readonly string $channelType,
        public readonly string $chatId,
        public readonly MessagePayload $payload,
        public readonly array $metadata = [],
    ) {
    }
}
