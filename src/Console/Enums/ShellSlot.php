<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * A place in the console shell where a Solution may mount a component.
 *
 * @experimental
 */
enum ShellSlot: string
{
    /** Floats over every console page, such as a chat bubble. */
    case Floating = 'floating';

    /** Sits in the shell header next to Core's own controls. */
    case Header = 'header';
}
