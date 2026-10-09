<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use InvalidArgumentException;

/**
 * The answer to {@see \Fapost\Foundation\Quota\Contracts\UsageMeterInterface::consume()}.
 */
final readonly class UsageDecision
{
    /**
     * @param  int|null  $limit  the limit for the period; null means no limit
     * @param  int|null  $used  units counted in the period after this call; null when unknown
     * @param  string|null  $message  the operator's text for people, in English; Core may show it
     */
    private function __construct(
        public bool $allowed,
        public ?int $limit,
        public ?int $used,
        public ?string $message,
    ) {
    }

    /**
     * @throws InvalidArgumentException when the limit or the used count is negative
     */
    public static function allowed(?int $limit = null, ?int $used = null): self
    {
        if ((null !== $limit && $limit < 0) || (null !== $used && $used < 0)) {
            throw new InvalidArgumentException('The limit and the used count cannot be negative.');
        }

        return new self(true, $limit, $used, null);
    }

    /**
     * @throws InvalidArgumentException when the limit or the used count is negative
     */
    public static function refused(int $limit, int $used, ?string $message = null): self
    {
        if ($limit < 0 || $used < 0) {
            throw new InvalidArgumentException('The limit and the used count cannot be negative.');
        }

        return new self(false, $limit, $used, $message);
    }
}
