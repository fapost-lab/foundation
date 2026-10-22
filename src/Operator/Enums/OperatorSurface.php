<?php

declare(strict_types=1);

namespace Fapost\Foundation\Operator\Enums;

/**
 * An operator screen: a tool that shows the whole installation, not one tenant.
 *
 * New surfaces may be added in a minor release. An implementation of
 * {@see \Fapost\Foundation\Operator\Contracts\OperatorAccessInterface} that meets a case it does
 * not know answers "no".
 */
enum OperatorSurface: string
{
    /** The queue dashboard (Laravel Horizon). */
    case Horizon = 'horizon';

    /** The server and application load dashboard (Laravel Pulse). */
    case Pulse = 'pulse';

    /** The request and job recorder (Laravel Telescope); it keeps request contents. */
    case Telescope = 'telescope';

    /** The log viewer. */
    case Logs = 'logs';

    /** The installation health screen. */
    case Health = 'health';
}
