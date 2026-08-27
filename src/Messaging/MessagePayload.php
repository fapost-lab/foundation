<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use Spatie\LaravelData\Data;

/**
 * Transport-agnostic outbound payload shared by all channel integrations.
 */
final class MessagePayload extends Data
{
    /**
     * @param  string  $type  Logical payload type such as text, photo, document, or keyboard.
     * @param  string  $text  Primary body text or caption associated with the payload.
     * @param  array<string, mixed>|null  $media
     * @param  array<string, mixed>|null  $keyboard
     */
    public function __construct(
        public readonly string $type,
        public readonly string $text,
        public readonly ?array $media = null,
        public readonly ?array $keyboard = null,
    ) {
    }
}
