<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel;

/**
 * Minimal channel identity exposed across the extension boundary.
 *
 * Concrete Eloquent channel models implement this so adapters living outside Core
 * (senders, media uploaders and downloaders) can read identity and provider credentials
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
     * `token` and `secret_token` are secrets: read them in the call that needs them, never keep, log or queue
     * them. `options` is the channel's settings (`channels.config`), plain values declared by its profile.
     *
     * @return array{token: string, secret_token: string, webhook_public_hash: string, options: array<string, mixed>}
     */
    public function getConfig(): array;
}
