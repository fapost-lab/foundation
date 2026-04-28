<?php

declare(strict_types=1);

namespace FAPost\Foundation\Media\DTO;

/**
 * Optional per-call context passed to channel media uploaders.
 *
 * Telegram requires a target chat id because file_id is only minted as a side effect
 * of a real send (upload-as-send). The caption travels with the upload so the very
 * first recipient receives the message exactly as authored — without it the upload-as-
 * send recipient would see the file without caption while subsequent cache-hit sends
 * include it. Channels with a true "upload" endpoint (WhatsApp, future providers) may
 * ignore the entire context.
 */
final readonly class UploadContext
{
    public function __construct(
        public ?string $targetChatId = null,
        public ?string $caption = null,
    ) {
    }
}
