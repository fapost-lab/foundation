<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * The dashboard a widget is placed on.
 *
 * @experimental
 */
enum DashboardTarget: string
{
    /** The dashboard of an assistant in the console. */
    case Console = 'console';

    /** The tenant's admin dashboard. */
    case Admin = 'admin';
}
