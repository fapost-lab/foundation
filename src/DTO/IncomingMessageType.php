<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

/**
 * Kind of an incoming message.
 *
 * `CallbackQuery` is a press on a {@see \Fapost\Foundation\Messaging\ButtonAction::Callback} button: the connector
 * reports it with `text` set to the button's payload verbatim. How the provider delivers the press (callback query,
 * action body, tracking data) is the connector's business.
 */
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
