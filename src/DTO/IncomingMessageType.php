<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

enum IncomingMessageType: string
{
    case Text          = 'text';
    case Photo         = 'photo';
    case Document      = 'document';
    case Audio         = 'audio';
    case Video         = 'video';
    case Voice         = 'voice';
    case Contact       = 'contact';
    case Location      = 'location';
    case CallbackQuery = 'callback_query';
    case Unknown       = 'unknown';
}
