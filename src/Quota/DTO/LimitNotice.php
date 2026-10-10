<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use InvalidArgumentException;

/**
 * What an operator package tells a tenant's admins to do when a limit refuses work, for example
 * "Upgrade to Pro to raise it". Written by the operator package, shown by Core in the limit
 * notification in place of Core's own "who to ask" text.
 */
final readonly class LimitNotice
{
    /**
     * @param  string  $message  the text, in the locale that was asked for
     * @param  string|null  $actionUrl  absolute https or http URL the action button opens
     *
     * @throws InvalidArgumentException when the message is empty, or an action has only a label or only a URL, or the URL is not http(s)
     */
    public function __construct(
        public string $message,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {
        if ('' === mb_trim($message)) {
            throw new InvalidArgumentException('A limit notice needs a message.');
        }

        if ((null === $actionLabel) !== (null === $actionUrl)) {
            throw new InvalidArgumentException('A limit notice action needs both a label and a URL.');
        }

        if (null !== $actionUrl && 1 !== preg_match('#^https?://#i', $actionUrl)) {
            throw new InvalidArgumentException('A limit notice action URL must be http or https.');
        }
    }
}
