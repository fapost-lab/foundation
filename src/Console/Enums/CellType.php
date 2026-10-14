<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * How Core renders the values of a Solution's table column.
 *
 * @experimental
 */
enum CellType: string
{
    case Text = 'text';

    case Badge = 'badge';

    case Number = 'number';

    case Date = 'date';

    /** A value coloured by the column's tones. */
    case Status = 'status';
}
