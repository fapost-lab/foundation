<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\Enums;

/**
 * What the value of a {@see \Fapost\Foundation\Health\DTO\HealthMetric} counts.
 *
 * New units may be added in a minor release.
 *
 * @experimental
 */
enum HealthUnit: string
{
    /** A whole number of things. */
    case Count = 'count';

    /** A duration in seconds. */
    case Seconds = 'seconds';

    /** A rate per minute. */
    case PerMinute = 'per_minute';

    /** A short status word, such as `matches`. */
    case Text = 'text';
}
