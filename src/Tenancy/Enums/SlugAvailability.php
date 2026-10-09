<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Whether a slug can be reserved right now, as {@see \Fapost\Foundation\Tenancy\Contracts\TenantReservationInterface::check()} sees it.
 *
 * The answer is advisory: reserving decides. New values may be added in a minor release; callers
 * matching on it keep a default arm.
 */
enum SlugAvailability: string
{
    case Available = 'available';
    case Invalid   = 'invalid';
    case Reserved  = 'reserved';
    case Taken     = 'taken';
}
