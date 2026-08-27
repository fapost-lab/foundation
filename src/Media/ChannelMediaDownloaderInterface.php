<?php

declare(strict_types=1);

namespace Fapost\Foundation\Media;

use Fapost\Foundation\Channel\ChannelInterface;
use Fapost\Foundation\Media\DTO\DownloadResult;

/**
 * Channel-specific media download adapter used by input nodes that ingest user files.
 */
interface ChannelMediaDownloaderInterface
{
    /**
     * Channel type identifier this adapter handles (matches {@see ChannelInterface::getType()}).
     */
    public function channelType(): string;

    /**
     * Download the file identified by the provider-side id and return its stream + metadata.
     */
    public function download(ChannelInterface $channel, string $providerFileId): DownloadResult;
}
