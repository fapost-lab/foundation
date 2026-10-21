<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

/**
 * What pressing a {@see Button} does, described by behaviour and not by where the channel draws it.
 *
 * A channel decides the placement (under the message, replacing the keyboard) and the wire format; the flow
 * only says what the press must achieve.
 *
 * Since foundation 0.26, experimental.
 */
enum ButtonAction: string
{
    /**
     * The press hands the button's payload back to the bot, and nothing appears in the chat.
     * The connector reports it as an incoming message of type `callback_query` whose `text` is the payload verbatim.
     */
    case Callback = 'callback';

    /**
     * The press sends the button's label to the bot as the user's own text message.
     */
    case Reply = 'reply';

    /**
     * The press sends the user's contact (phone number) to the bot.
     */
    case ShareContact = 'share_contact';

    /**
     * The press sends the user's location to the bot.
     */
    case ShareLocation = 'share_location';
}
