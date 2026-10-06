<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Exceptions;

use RuntimeException;

/**
 * Thrown by {@see \Fapost\Foundation\Quota\Contracts\RecordQuotaInterface::assertCanCreate()} when
 * the tenant already has as many records as its limit allows. The message is meant for people.
 */
final class RecordLimitReachedException extends RuntimeException
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int $limit,
        public readonly int $current,
    ) {
        parent::__construct(sprintf('%s limit reached: %d of %d.', $label, $current, $limit));
    }
}
