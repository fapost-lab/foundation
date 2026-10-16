<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

use Fapost\Foundation\Solution\Enums\SolutionPricePeriod;
use InvalidArgumentException;

/**
 * The price an operator quotes for a Solution, for display only: Core formats it in the reader's language and never
 * charges it.
 *
 * @experimental
 */
final readonly class SolutionPrice
{
    /**
     * @param  int  $amountMinor  the amount in the currency's minor units (cents), zero or more
     * @param  string  $currency  ISO 4217 code in upper case, such as "EUR"
     * @param  int  $periodCount  how many period units the amount covers, one or more
     *
     * @throws InvalidArgumentException on a negative amount, a currency that is not three upper-case letters, or a count below one
     */
    public function __construct(
        public int $amountMinor,
        public string $currency,
        public SolutionPricePeriod $period = SolutionPricePeriod::Month,
        public int $periodCount = 1,
    ) {
        if ($amountMinor < 0) {
            throw new InvalidArgumentException('A price cannot be negative.');
        }

        if (1 !== preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('A price currency must be an ISO 4217 code in upper case.');
        }

        if ($periodCount < 1) {
            throw new InvalidArgumentException('A price period count must be one or more.');
        }
    }
}
