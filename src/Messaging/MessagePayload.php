<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use Spatie\LaravelData\Data;

/**
 * Transport-agnostic outbound payload shared by all channel integrations.
 *
 * For a channel that sends media by URL ({@see \Fapost\Foundation\Media\ChannelMediaLinkPolicyInterface}),
 * `media` holds: `url` (a signed public link that expires; never log or store it), `mime_type`, `size` (bytes),
 * `file_name` (with its extension) and `kind` (`image`, `video`, `audio` or `document`). `type` is then only a
 * hint shaped for channels that upload; choose the provider's message type from `mime_type`.
 *
 * `buttons` is the channel-neutral description of a message's buttons. `type` stays `keyboard` for a message with
 * buttons. A connector translates the {@see ButtonSet} into its provider's format; what the channel can show is
 * declared by {@see ChannelCapabilities}.
 *
 * The `remove_keyboard` payload type, with `metadata.edit_message_id` naming the sent message, is the neutral
 * operation "strip the buttons of a sent message". It is sent only to a channel that declares
 * {@see ChannelCapability::RemoveButtons}.
 */
final class MessagePayload extends Data
{
    /**
     * @param  string  $type  Logical payload type such as text, photo, document, or keyboard.
     * @param  string  $text  Primary body text or caption associated with the payload.
     * @param  array<string, mixed>|null  $media
     * @param  array<string, mixed>|null  $keyboard  Raw provider markup (Telegram Bot API shape). Telegram reads it
     *                                                only when `buttons` is null.
     * @param  ButtonSet|null  $buttons  Channel-neutral buttons of the message (since foundation 0.26, experimental).
     */
    public function __construct(
        public readonly string $type,
        public readonly string $text,
        public readonly ?array $media = null,
        /** @deprecated since foundation 0.26: use `buttons`. Removed when Telegram's tests move with its module. */
        public readonly ?array $keyboard = null,
        public readonly ?ButtonSet $buttons = null,
    ) {
    }
}
