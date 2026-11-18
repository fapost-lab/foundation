<?php

declare(strict_types=1);

namespace Fapost\Foundation\Media;

/**
 * Marker for a {@see ChannelMediaUploaderInterface} that fills {@see DTO\UploadResult::$providerKind}.
 *
 * Core keeps the reported kind with the cached file id and sends the id back by the method that matches it. A cached
 * ref that has no kind therefore predates the uploader's reporting one: Core drops it and uploads again, once, so the
 * kind gets stored. An uploader that does not carry the marker keeps its refs as they are.
 */
interface ReportsProviderKind
{
}
