<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

/**
 * Normalized outgoing message for sending through a ChannelAdapter.
 */
final readonly class OutgoingMessage
{
    public function __construct(
        /** External chat ID (platform-specific) */
        public string $externalChatId,

        /** Message text */
        public ?string $text,

        /** Message type */
        public OutgoingMessageType $type,

        /**
         * Platform-specific parameters.
         * For example: reply_markup (keyboard), parse_mode, caption, file_id.
         *
         * @var array<string, mixed>
         */
        public array $payload = [],
    ) {
    }
}
