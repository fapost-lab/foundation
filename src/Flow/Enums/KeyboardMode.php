<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Enums;

enum KeyboardMode: string
{
    case Inline = 'inline';
    case Reply  = 'reply';
}
