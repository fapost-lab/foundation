<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Enums;

/**
 * The unit of the period a Solution's price is quoted for.
 *
 * @experimental
 */
enum SolutionPricePeriod: string
{
    case Day   = 'day';
    case Week  = 'week';
    case Month = 'month';
    case Year  = 'year';
}
