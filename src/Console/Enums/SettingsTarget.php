<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * The settings screen a tab is added to.
 *
 * @experimental
 */
enum SettingsTarget: string
{
    /** An assistant's settings. */
    case Assistant = 'assistant';

    /** The tenant's settings. */
    case Tenant = 'tenant';
}
