<?php

declare(strict_types=1);

namespace FAPost\Foundation\Channel\Ingress;

/**
 * Opt-in contract for channel adapters whose webhook authentication can be
 * expressed declaratively.
 *
 * Implementing this interface is what makes a channel eligible for ingress
 * runtimes outside PHP: the spec is published to the shared registry and any
 * gateway can verify the channel's webhooks without knowing the platform.
 *
 * Adapters that need verification logic richer than {@see IngressSpec} can
 * express — signing over a composed string, enforcing request age, multi-step
 * challenges — simply do not implement this interface. Their webhooks stay on
 * the PHP ingress path, which remains fully supported.
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
