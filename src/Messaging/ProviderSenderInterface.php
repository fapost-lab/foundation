<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

use Fapost\Foundation\Channel\ChannelInterface;

/**
 * Contract implemented by concrete transport senders.
 */
interface ProviderSenderInterface
{
    /**
     * Deliver the message through one concrete provider integration.
     *
     * `$channel` is the message's channel, loaded by Core in the request or job that sends it. Read the
     * credentials from {@see ChannelInterface::getConfig()} during this call; never keep them, log them or
     * put them into the result or an exception message.
     *
     * Since foundation 0.31 the channel is an argument and the envelope carries no transport token.
     */
    public function deliver(OutboundMessage $message, ChannelInterface $channel): DeliveryResult;
}
