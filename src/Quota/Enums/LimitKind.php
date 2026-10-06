<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Enums;

/**
 * What a limit counts.
 *
 * New kinds may be added in a minor release: callers matching on it keep a default arm.
 */
enum LimitKind: string
{
    /** Records that exist at a time, such as assistants. Deleting one frees a slot. */
    case Records = 'records';

    /** Units used within a billing period, such as outbound messages. Resets each period. */
    case PerPeriod = 'per_period';

    /** Stored bytes, such as media storage. */
    case Bytes = 'bytes';
}
