<?php

declare(strict_types=1);

namespace FAPost\Foundation\Channel;

/**
 * Transport snapshot passed to channel webhook registrars.
 *
 * Contains only the data needed for provider-side webhook operations — no Eloquent dependency.
 *
 * @param  array<string, mixed>  $config
 */
final readonly class WebhookRegistrationPayload
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        public string $channelId,
        public string $token,
        public string $secretToken,
        public string $webhookPublicHash,
        public array $config = [],
    ) {
    }
}
