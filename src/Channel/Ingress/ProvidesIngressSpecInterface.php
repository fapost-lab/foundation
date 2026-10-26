<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Ingress;

/**
 * Opt-in contract for channel adapters whose webhook authentication can be
 * expressed declaratively.
 *
 * Implementing this interface is what makes a channel eligible for ingress
 * runtimes outside PHP: the spec is published to the shared registry and any
 * gateway can verify the channel's webhooks without knowing the platform.
 *
 * Connector adapters ({@see \Fapost\Foundation\Channel\ChannelInboundAdapterInterface}) always provide a
 * spec: there is no PHP verification fallback for them. A scheme the spec cannot express extends the spec
 * ({@see IngressSpec}, {@see SignatureScheme}); it is not worked around in the adapter.
 *
 * Kept separate from the adapter interface so that adding declarative ingress
 * never breaks an existing adapter, including ones shipped by plugins.
 */
interface ProvidesIngressSpecInterface
{
    /**
     * Declarative description of this platform's signature and idempotency rules.
     */
    public function ingressSpec(): IngressSpec;
}
