<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel;

/**
 * Contract for provider-side webhook registration lifecycle.
 */
interface WebhookRegistrarInterface
{
    /**
     * Create or refresh the external webhook for the given channel payload.
     */
    public function register(WebhookRegistrationPayload $payload): void;

    /**
     * Remove the external webhook for the given channel payload.
     */
    public function deregister(WebhookRegistrationPayload $payload): void;
}
