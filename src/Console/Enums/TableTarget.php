<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * A Core table a Solution may add a column to.
 *
 * @experimental
 */
enum TableTarget: string
{
    case Contacts = 'contacts';

    case Conversations = 'conversations';
}
