<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel;

use Fapost\Foundation\Channel\Ingress\ProvidesIngressSpecInterface;
use Fapost\Foundation\DTO\IncomingMessage;

/**
 * The inbound half of a connector's channel: turns a provider webhook body into an {@see IncomingMessage}.
 *
 * Signature verification and the idempotency key are declared only through {@see ingressSpec()} and
 * executed by Core and the ingress gateway; a connector ships no verification code. normalize() runs
 * in the worker, never in ingress. When a provider's signature cannot be expressed by
 * {@see Ingress\SignatureScheme}, the scheme is extended, not this contract.
 *
 * Since foundation 0.23, experimental.
 */
interface ChannelInboundAdapterInterface extends ProvidesIngressSpecInterface
{
    /**
     * Turn the decoded webhook body into the platform's incoming message.
     *
     * @param  array<string, mixed>  $payload  the decoded webhook body
     *
     * @throws InboundEventIgnoredException when the delivery is not a user's message for the flow (the bot's own
     *                                      message, an edit, a system event); Core drops it without a retry, a
     *                                      contact or a transcript
     */
    public function normalize(array $payload): IncomingMessage;
}
