<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

/**
 * Optional companion of {@see ChannelProfileInterface}: the steps a person follows on the provider's side to connect
 * a channel. A profile that also implements this interface gets a guide in the console; one that does not has none.
 *
 * The method is pure and does no I/O.
 *
 * @experimental Since foundation 0.33.
 */
interface ChannelSetupGuideInterface
{
    /**
     * @param  string|null           $webhookUrl  the channel's public webhook URL; null for a channel not yet created
     * @param  array<string, mixed>  $config      the channel's stored `config`; empty for a channel not yet created
     *
     * @return list<ChannelSetupStep>
     */
    public function setupGuide(?string $webhookUrl, array $config): array;
}
