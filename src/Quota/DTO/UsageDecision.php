<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use DateTimeImmutable;
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
     * @param  DateTimeImmutable|null  $periodEndsAt  when the period this decision belongs to ends, on a refusal and on an
     *                                                  approaching warning; Core sends one notification per limit and
     *                                                  period, and uses the calendar month (UTC) when the operator does
     *                                                  not say. Core ignores it on a plain allowed decision
     * @param  bool  $approaching  true on an allowed decision that asks Core to warn the tenant's admins that the
     *                             limit is close; see {@see self::approaching()}
     */
    private function __construct(
        public bool $allowed,
        public ?int $limit,
        public ?int $used,
        public ?string $message,
        public ?DateTimeImmutable $periodEndsAt = null,
        public bool $approaching = false,
    ) {
    }

    /**
     * @throws InvalidArgumentException when the limit or the used count is negative
     */
    public static function allowed(?int $limit = null, ?int $used = null, ?DateTimeImmutable $periodEndsAt = null): self
    {
        if ((null !== $limit && $limit < 0) || (null !== $used && $used < 0)) {
            throw new InvalidArgumentException('The limit and the used count cannot be negative.');
        }

        return new self(true, $limit, $used, null, $periodEndsAt);
    }

    /**
     * Allowed, and the operator asks Core to warn the tenant's admins that the limit is close.
     *
     * Flag the unit that brings the period's use to the operator's warning threshold, once per
     * tenant, key and period. The unit is allowed like any other; $used may exceed $limit by the
     * number of parallel workers. Pass $periodEndsAt so Core sends one warning per period.
     *
     * @throws InvalidArgumentException when the limit is below one or the used count is negative
     */
    public static function approaching(int $limit, int $used, ?DateTimeImmutable $periodEndsAt = null): self
    {
        if ($limit < 1 || $used < 0) {
            throw new InvalidArgumentException('The limit must be at least one and the used count cannot be negative.');
        }

        return new self(true, $limit, $used, null, $periodEndsAt, true);
    }

    /**
     * @throws InvalidArgumentException when the limit or the used count is negative
     */
    public static function refused(int $limit, int $used, ?string $message = null, ?DateTimeImmutable $periodEndsAt = null): self
    {
        if ($limit < 0 || $used < 0) {
            throw new InvalidArgumentException('The limit and the used count cannot be negative.');
        }

        return new self(false, $limit, $used, $message, $periodEndsAt);
    }
}
