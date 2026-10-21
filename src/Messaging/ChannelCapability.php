<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

/**
 * Something a channel can do beyond text and media, which a flow may rely on.
 *
 * Since foundation 0.26, experimental.
 */
enum ChannelCapability: string
{
    /**
     * Shows {@see ButtonAction::Callback} buttons and reports their presses.
     */
    case CallbackButtons = 'callback_buttons';

    /**
     * Shows {@see ButtonAction::Reply} buttons.
     */
    case ReplyButtons = 'reply_buttons';

    /**
     * Shows a {@see ButtonAction::ShareContact} button.
     */
    case ShareContact = 'share_contact';

    /**
     * Shows a {@see ButtonAction::ShareLocation} button.
     */
    case ShareLocation = 'share_location';

    /**
     * Can strip the buttons from a message already sent (the `remove_keyboard` payload type).
     */
    case RemoveButtons = 'remove_buttons';

    /**
     * Callback buttons of older messages stay pressable after newer messages were sent.
     */
    case PersistentButtons = 'persistent_buttons';
}
