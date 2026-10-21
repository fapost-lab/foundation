<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

/**
 * What Core cannot know about a channel type: its settings and the public link of a configured channel.
 *
 * Core renders the channel form, builds the request rules and stores `channels.config` from
 * {@see settings()}, and shows {@see publicLink()} wherever a person is told how to reach the bot. A channel
 * type without a profile has no settings, any secret token and no link.
 *
 * Built once, outside the tenant, to read the declaration; the constructor must be cheap and touch no
 * tenant data. The methods are pure.
 *
 * @experimental
 */
interface ChannelProfileInterface
{
    /**
     * The channel type this profile describes; Core checks it against the type it was declared for.
     */
    public function channelType(): string;

    public function settings(): ChannelSettingsSchema;

    /**
     * The public link of a channel, from its stored `config` (managed fields included); null while unknown.
     *
     * @param  array<string, mixed>  $config
     */
    public function publicLink(array $config): ?ChannelPublicLink;
}
