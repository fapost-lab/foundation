<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

use Fapost\Foundation\Media\Enums\MediaKind;

/**
 * Structured media payload extracted from an incoming channel message.
 *
 * Carries provider-side identifiers and metadata so input nodes can hand off ingestion
 * to the media domain without grubbing inside platform-specific payload arrays.
 *
 * `providerKind` is how the provider stores this file, in its own vocabulary, as the connector would send it back by
 * its id (Telegram: `photo`, `video`, `audio`, `voice` or `document`) — the same vocabulary as
 * {@see \Fapost\Foundation\Media\DTO\UploadResult::$providerKind}. It belongs to the file, not to the message: one
 * message may carry several files of different kinds. Null when the provider has no such notion or the connector
 * does not say.
 */
final readonly class IncomingMedia
{
    public function __construct(
        public string $providerFileId,
        public MediaKind $kind,
        public ?string $mimeType = null,
        public ?string $fileName = null,
        public ?int $size = null,
        public ?string $providerKind = null,
    ) {
    }
}
