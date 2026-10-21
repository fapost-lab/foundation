<?php

declare(strict_types=1);

namespace Fapost\Foundation\Media;

use Fapost\Foundation\Media\Enums\MediaKind;

/**
 * The largest files a provider accepts, so Core can warn before a person picks a file the channel cannot send.
 *
 * Registered by tagging the class `media.channel.limits`; Core builds it inside the request that uses it.
 *
 * @experimental
 */
interface ChannelMediaLimitsInterface
{
    /**
     * Channel type these limits apply to.
     */
    public function channelType(): string;

    /**
     * Largest size in bytes the provider accepts for this kind of media; null when it has no limit or none is known.
     */
    public function maxBytes(MediaKind $kind): ?int;
}
