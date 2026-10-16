<?php

declare(strict_types=1);

namespace Fapost\Foundation\Media;

/**
 * Declares that a channel type sends media by a public URL instead of uploading bytes.
 *
 * Some providers (Viber) accept a picture, a video or a file only as a URL they fetch themselves. A channel
 * that registers this policy instead of a {@see ChannelMediaUploaderInterface} receives, in the outgoing
 * payload, a signed public link to the media file that Core issued just before delivery. The channel never
 * issues links itself. When a channel type has both an uploader and a link policy, the uploader wins.
 *
 * Payload keys a link-delivered media message carries in `MessagePayload::$media`: see
 * {@see \Fapost\Foundation\Messaging\MessagePayload}.
 */
interface ChannelMediaLinkPolicyInterface
{
    /**
     * Channel type identifier this policy applies to (matches {@see \Fapost\Foundation\Channel\ChannelInterface::getType()}).
     */
    public function channelType(): string;

    /**
     * Seconds the link must stay valid after it is issued, or null for Core's default.
     *
     * Core caps the value at its own maximum and never issues a link shorter than a minute, so a provider that
     * fetches the file later than it receives the message can ask for more time without being able to ask for
     * an unbounded one.
     */
    public function linkTtlSeconds(): ?int;
}
