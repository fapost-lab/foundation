<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Normalized incoming message from any channel.
 *
 * ChannelAdapter parses platform-specific payload and returns this object.
 * The flow engine works only with IncomingMessage and knows nothing about Telegram/WhatsApp specifics.
 */
final readonly class IncomingMessage
{
    public function __construct(
        /**
         * Unique platform update ID.
         * Used as an idempotency key (processed:{updateId}).
         */
        public string $updateId,

        /** External user ID on the platform */
        public string $externalUserId,

        /** External chat/conversation ID */
        public string $externalChatId,

        /** Message text (null for media messages without caption) */
        public ?string $text,

        /** Message type */
        public IncomingMessageType $type,

        /** Source platform */
        public string $platform,

        /**
         * Additional platform-specific payload.
         * For example: callback_data, file_id, contact, location.
         *
         * @var array<string, mixed>
         */
        public array $payload = [],
    )
    {
    }
}
