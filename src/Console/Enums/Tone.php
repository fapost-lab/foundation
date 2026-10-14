<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * The colour of a status cell, named by meaning; the console theme picks the actual colour.
 *
 * @experimental
 */
enum Tone: string
{
    case Neutral = 'neutral';

    case Success = 'success';

    case Warning = 'warning';

    case Danger = 'danger';

    case Info = 'info';
}
