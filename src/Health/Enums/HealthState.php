<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\Enums;

/**
 * What a probe could establish about a service.
 *
 * Not a traffic light: a source that could not be read is {@see self::Unknown}, never folded into
 * "up" or "down". A renderer that meets a case it does not know shows it as unknown.
 *
 * @experimental
 */
enum HealthState: string
{
    /** The service answered and reported itself healthy. */
    case Up = 'up';

    /** The service answered, and what it said is that it is not healthy. */
    case Down = 'down';

    /** The source could not be read: unreachable, timed out, rejected the request, not configured, or the probe failed. */
    case Unknown = 'unknown';

    /** The service is switched off on purpose. Not an error. */
    case Disabled = 'disabled';
}
