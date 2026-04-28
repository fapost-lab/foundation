<?php

declare(strict_types=1);

namespace FAPost\Foundation\Channel;

/**
 * Minimal channel identity exposed across the extension boundary.
 *
 * Concrete Eloquent channel models implement this so adapters living outside Core
 * (media uploaders, future plugin senders) can read identity and provider credentials
 * without depending on App\* classes.
 */
interface ChannelInterface
{
    /**
     * Stable channel identifier (ULID stored as RFC-4122 UUID lowercase).
     */
    public function getId(): string;

    /**
     * Channel type as a stable string identifier (e.g. "telegram", "whatsapp").
     *
     * String — not enum — to keep Foundation free of Core taxonomies; the Core enum
     * casts to/from this value at the boundary.
     */
    public function getType(): string;

    /**
     * Provider transport credentials and per-channel options needed by adapters.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}
