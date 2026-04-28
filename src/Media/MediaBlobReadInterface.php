<?php

declare(strict_types=1);

namespace FAPost\Foundation\Media;

use Psr\Http\Message\StreamInterface;

/**
 * Read-only view of a stored media blob.
 *
 * Channel media uploaders consume this contract — never an Eloquent model — so the
 * extension boundary stays clean of Core types. Implementations open the underlying
 * storage stream lazily.
 */
interface MediaBlobReadInterface
{
    /**
     * Stable blob identifier (ULID).
     */
    public function getId(): string;

    /**
     * Lowercase hex SHA-256 of the file content. Used for deduplication.
     */
    public function getContentHash(): string;

    /**
     * IANA mime type as detected at upload time.
     */
    public function getMimeType(): string;

    /**
     * Size in bytes.
     */
    public function getSize(): int;

    /**
     * Open a fresh read stream over the blob.
     *
     * Caller is responsible for closing the stream.
     */
    public function openStream(): StreamInterface;
}
