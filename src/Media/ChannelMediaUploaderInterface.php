<?php

declare(strict_types=1);

namespace FAPost\Foundation\Media;

use FAPost\Foundation\Channel\ChannelInterface;
use FAPost\Foundation\Media\DTO\UploadContext;
use FAPost\Foundation\Media\DTO\UploadResult;

/**
 * Channel-specific media upload adapter.
 *
 * Implementations are registered per channel type and invoked lazily by the dispatcher
 * the first time a blob is sent through a given channel.
 */
interface ChannelMediaUploaderInterface
{
    /**
     * Channel type identifier this adapter handles (matches {@see ChannelInterface::getType()}).
     */
    public function channelType(): string;

    /**
     * Upload the blob to the provider and return its provider-side file id.
     *
     * `$context` carries optional per-call data (e.g. Telegram requires a target chat id
     * because file_id is only minted as a side effect of a real send). Implementations
     * that do not need context should ignore it.
     *
     * @throws \RuntimeException when the provider rejects the upload (use a typed
     *                           exception in concrete implementations).
     */
    public function upload(
        MediaBlobReadInterface $blob,
        ChannelInterface $channel,
        ?UploadContext $context = null,
    ): UploadResult;
}
