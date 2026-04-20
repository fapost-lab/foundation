<?php

declare(strict_types=1);

namespace FAPost\Foundation\Channel;

use App\Domains\Assistant\Models\Channel;

/**
 * Contract for provider-side webhook registration lifecycle.
 */
interface WebhookRegistrarInterface
{
    /**
     * Create or refresh the external webhook for the given channel.
     */
    public function register(Channel $channel): void;

    /**
     * Remove the external webhook for the given channel.
     */
    public function deregister(Channel $channel): void;
}
