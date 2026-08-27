<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

enum OutgoingMessageType: string
{
    case Text     = 'text';
    case Photo    = 'photo';
    case Document = 'document';
    case Audio    = 'audio';
    case Video    = 'video';
}
