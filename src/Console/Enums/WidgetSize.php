<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * How much of a dashboard row a widget takes.
 *
 * @experimental
 */
enum WidgetSize: string
{
    /** One third of a row. */
    case Single = 'single';

    /** Two thirds of a row. */
    case Double = 'double';

    /** The whole row. */
    case Full = 'full';
}
