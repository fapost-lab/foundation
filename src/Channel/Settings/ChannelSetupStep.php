<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

use InvalidArgumentException;

/**
 * One step of a channel's setup guide: what a person does on the provider's side, with an optional value to copy.
 *
 * @experimental Since foundation 0.33.
 */
final readonly class ChannelSetupStep
{
    /**
     * @param  string       $title           short imperative heading, in English
     * @param  string|null  $text            longer explanation, in English
     * @param  string|null  $copy            a value to copy, such as the webhook URL or a manifest
     * @param  string|null  $translationKey  prefix under which Core looks up `{prefix}.title` and `{prefix}.text`; English is the fallback
     *
     * @throws InvalidArgumentException when the title is empty
     */
    public function __construct(
        public string $title,
        public ?string $text = null,
        public ?string $copy = null,
        public ChannelSetupCopyKind $copyKind = ChannelSetupCopyKind::Text,
        public ?string $translationKey = null,
    ) {
        if ('' === $title) {
            throw new InvalidArgumentException('A channel setup step needs a title.');
        }
    }
}
