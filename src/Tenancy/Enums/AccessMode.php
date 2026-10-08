<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * What a tenant may do right now, as an operator package decides it.
 *
 * New modes may be added in a minor release: callers matching on it keep a default arm.
 */
enum AccessMode: string
{
    /** Everything runs. */
    case Active = 'active';

    /**
     * Inbound is not processed and scheduled work waits; the platform refuses changes where it can
     * (how far that reaches is the platform's to say). Data is kept.
     */
    case Stopped = 'stopped';
}
