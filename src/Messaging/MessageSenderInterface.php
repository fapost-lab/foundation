<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

/**
 * Generic outbound messaging entry point used by queue jobs and orchestration.
 */
interface MessageSenderInterface
{
    /**
     * Send one prepared outbound message through its resolved channel integration.
     *
     * @throws \Fapost\Foundation\Quota\Exceptions\VolumeLimitReachedException when the tenant's outbound volume
     *         is used up for the period; nothing was sent and the caller must not retry
     */
    public function send(OutboundMessage $message): DeliveryResult;
}
