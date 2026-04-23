<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Enums;

enum KeyboardMode: string
{
    case Inline = 'inline';
    case Reply = 'reply';
}
