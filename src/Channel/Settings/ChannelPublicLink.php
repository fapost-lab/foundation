<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

use InvalidArgumentException;

/**
 * How a person finds and opens the channel's bot or account.
 *
 * @experimental
 */
final readonly class ChannelPublicLink
{
    /**
     * @param  string  $username  as the provider writes it, without a leading "@"
     * @param  string  $handle  how a person addresses it, such as "@acme_bot"
     * @param  string|null  $url  a link that opens the chat, such as "https://t.me/acme_bot"; null when the provider has none
     *
     * @throws InvalidArgumentException when the username or the handle is empty
     */
    public function __construct(
        public string $username,
        public string $handle,
        public ?string $url = null,
    ) {
        if ('' === $username || '' === $handle) {
            throw new InvalidArgumentException('A channel public link needs a username and a handle.');
        }
    }
}
