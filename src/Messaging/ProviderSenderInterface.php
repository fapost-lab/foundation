<?php

declare(strict_types=1);

namespace FAPost\Foundation\Messaging;

/**
 * Contract implemented by concrete transport senders.
 */
interface ProviderSenderInterface
{
    /**
     * Deliver the message through one concrete provider integration.
     */
    public function deliver(OutboundMessage $message): DeliveryResult;
}
