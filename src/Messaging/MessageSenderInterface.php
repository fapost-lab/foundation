<?php

declare(strict_types=1);

namespace FAPost\Foundation\Messaging;

/**
 * Generic outbound messaging entry point used by queue jobs and orchestration.
 */
interface MessageSenderInterface
{
    /**
     * Send one prepared outbound message through its resolved channel integration.
     */
    public function send(OutboundMessage $message): DeliveryResult;
}
