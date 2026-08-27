<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

use Fapost\Foundation\Media\Enums\MediaKind;

/**
 * Structured media payload extracted from an incoming channel message.
 *
 * Carries provider-side identifiers and metadata so input nodes can hand off ingestion
 * to the media domain without grubbing inside platform-specific payload arrays.
 */
final readonly class IncomingMedia
{
    public function __construct(
        public string $providerFileId,
        public MediaKind $kind,
        public ?string $mimeType = null,
        public ?string $fileName = null,
        public ?int $size = null,
    ) {
    }
}
