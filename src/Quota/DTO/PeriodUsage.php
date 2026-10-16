<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * How much of one per-period limit a tenant has used in its current period, as reported by
 * {@see \Fapost\Foundation\Quota\Contracts\PeriodUsageInterface}.
 */
final readonly class PeriodUsage
{
    /**
     * @param  string  $key  a key registered with {@see \Fapost\Foundation\Quota\Enums\LimitKind::PerPeriod}
     * @param  int  $used  units counted in the period, at least 0; may exceed the limit by the number of parallel workers
     * @param  int|null  $limit  the limit for the period, at least 0; null means no limit
     * @param  DateTimeImmutable  $periodStartsAt  inclusive
     * @param  DateTimeImmutable  $periodEndsAt  exclusive, after the start; the same end the meter passes in {@see UsageDecision}
     *
     * @throws InvalidArgumentException when the key is empty, a number is negative or the period does not end after it starts
     */
    public function __construct(
        public string $key,
        public int $used,
        public ?int $limit,
        public DateTimeImmutable $periodStartsAt,
        public DateTimeImmutable $periodEndsAt,
    ) {
        if ('' === mb_trim($key)) {
            throw new InvalidArgumentException('A period usage needs a limit key.');
        }

        if ($used < 0 || (null !== $limit && $limit < 0)) {
            throw new InvalidArgumentException('The used count and the limit cannot be negative.');
        }

        if ($periodEndsAt <= $periodStartsAt) {
            throw new InvalidArgumentException('The period must end after it starts.');
        }
    }
}
