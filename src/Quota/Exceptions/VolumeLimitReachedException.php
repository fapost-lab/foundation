<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Exceptions;

use RuntimeException;

/**
 * Thrown by {@see \Fapost\Foundation\Messaging\MessageSenderInterface::send()} when the tenant has
 * used up a per-period volume limit, such as outbound messages. Nothing was sent; callers must not
 * retry, because the limit does not lift until the period ends or the limit is raised. The message
 * is meant for people.
 */
final class VolumeLimitReachedException extends RuntimeException
{
    public function __construct(
        public readonly string $key,
        public readonly ?int $limit = null,
        public readonly ?int $used = null,
        ?string $message = null,
    ) {
        parent::__construct($message ?? (null !== $limit && null !== $used
            ? sprintf('Limit "%s" reached: %d of %d this period.', $key, $used, $limit)
            : sprintf('Limit "%s" reached for this period.', $key)));
    }
}
