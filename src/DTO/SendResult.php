<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Result of sending a message through a ChannelAdapter.
 */
final readonly class SendResult
{
    public function __construct(
        public bool $success,

        /**
         * Platform-specific ID of the sent message.
         * Used for deduplication (sent_message_ids) in the send_message handler.
         */
        public ?string $platformMessageId = null,

        /** Error message when success = false */
        public ?string $errorMessage = null,

        /** Platform HTTP response code */
        public ?int $httpStatus = null,
    ) {
    }

    public static function ok(string $platformMessageId): self
    {
        return new self(success: true, platformMessageId: $platformMessageId);
    }

    public static function fail(string $errorMessage, ?int $httpStatus = null): self
    {
        return new self(success: false, errorMessage: $errorMessage, httpStatus: $httpStatus);
    }
}
