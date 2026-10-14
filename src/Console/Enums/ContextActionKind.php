<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * What a context action does when chosen.
 *
 * @experimental
 */
enum ContextActionKind: string
{
    /** Navigates to a route of the Solution; the route takes a `record` parameter. */
    case Link = 'link';

    /** Opens a component of the Solution in a dialog. */
    case Dialog = 'dialog';
}
