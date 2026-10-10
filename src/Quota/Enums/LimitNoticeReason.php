<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\Enums;

/**
 * Why Core asks an operator package for a limit notice.
 *
 * New reasons may be added in a minor release: implementations keep a default arm.
 */
enum LimitNoticeReason: string
{
    /** Work was turned away under the limit. */
    case Refused = 'refused';

    /** A Records limit has just been filled: the last place was taken and nothing was refused yet. */
    case Reached = 'reached';
}
