<?php

declare(strict_types=1);

namespace Fapost\Foundation\Auth\Enums;

/**
 * The way a one-time code reaches the contact who has to type it back.
 *
 * @experimental
 */
enum OneTimeCodeChannel: string
{
    case Sms   = 'sms';
    case Email = 'email';
}
