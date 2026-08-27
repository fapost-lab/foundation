<?php

declare(strict_types=1);

namespace Fapost\Foundation\Media\DTO;

use Carbon\CarbonImmutable;
use Psr\Http\Message\StreamInterface;

/**
 * Outcome of a channel-side media download (used by input nodes that ingest user files).
 *
 * Stream is consumer-owned; downloader implementations must not hold references to it
 * after returning.
 */
final readonly class DownloadResult
{
    public function __construct(
        public StreamInterface $stream,
        public string $mimeType,
        public int $size,
        public ?string $originalFilename = null,
        public ?CarbonImmutable $expiresAt = null,
    ) {
    }
}
