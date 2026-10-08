<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use InvalidArgumentException;

/**
 * What a tenant's staff are told about the tenant's access mode, for example "Your trial has ended".
 * Written by the operator package, shown by Core as a banner.
 */
final readonly class AccessNotice
{
    /**
     * @param  string|null  $actionUrl  absolute https or http URL the action button opens
     *
     * @throws InvalidArgumentException when the title is empty, or an action has only a label or only a URL, or the URL is not http(s)
     */
    public function __construct(
        public string $title,
        public ?string $message = null,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {
        if (trim($title) === '') {
            throw new InvalidArgumentException('An access notice needs a title.');
        }

        if (($actionLabel === null) !== ($actionUrl === null)) {
            throw new InvalidArgumentException('An access notice action needs both a label and a URL.');
        }

        if ($actionUrl !== null && preg_match('#^https?://#i', $actionUrl) !== 1) {
            throw new InvalidArgumentException('An access notice action URL must be http or https.');
        }
    }
}
