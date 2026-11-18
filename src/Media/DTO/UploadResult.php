<?php

declare(strict_types=1);

namespace Fapost\Foundation\Media\DTO;

use Carbon\CarbonImmutable;

/**
 * Outcome of a successful channel-side media upload.
 *
 * `expiresAt` is null for providers with long-lived assets (Telegram). For TTL-bounded
 * providers (WhatsApp, 30 day TTL) the dispatcher uses it to schedule re-upload.
 *
 * `deliveredMessageId` is set when the uploader had to perform an upload-as-send
 * (Telegram has no upload-without-send endpoint): the bytes were delivered to the
 * target chat as a real message and the dispatcher must signal this so the calling
 * handler does not deliver a duplicate.
 *
 * `providerKind` is what the provider says it stored, in its own vocabulary (Telegram: `photo`, `video`, `audio`,
 * `voice` or `document`), when that can differ from what the uploader asked for. A later send of the cached id
 * has to use the method that matches it. Null when the provider does not say.
 */
final readonly class UploadResult
{
    public function __construct(
        public string $providerFileId,
        public ?CarbonImmutable $expiresAt = null,
        public ?string $deliveredMessageId = null,
        public ?string $providerKind = null,
    ) {
    }
}
