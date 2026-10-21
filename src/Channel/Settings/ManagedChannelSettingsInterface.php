<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Settings;

/**
 * Where an integration hands Core the values it read from the provider for the managed fields of its channel's schema
 * ({@see ChannelSettingField::$managed}), such as a bot name or an account URI. Core checks them against the schema
 * and stores them; an integration never writes the channel itself.
 *
 * Inject it into the connector's webhook registrar and call it after the provider answered: the registration payload
 * carries the channel's id ({@see \Fapost\Foundation\Channel\WebhookRegistrationPayload::$channelId}). It needs the
 * channel's id and the channel's tenant, so a service that has neither (an inbound adapter's `normalize()`) cannot
 * call it yet.
 *
 * Since foundation 0.28, experimental.
 *
 * @experimental
 */
interface ManagedChannelSettingsInterface
{
    /**
     * Store values the integration read from the provider for the managed fields of the channel's schema.
     * A null value removes the key. Runs in the current tenant; the channel must belong to it. A channel that no
     * longer exists is ignored.
     *
     * @param  array<string, scalar|list<scalar>|null>  $values  managed field key => value
     *
     * @throws InvalidManagedSettingException  a key that is not a managed field of the channel's schema, or a
     *                                         value that does not fit the field's type
     */
    public function report(string $channelId, array $values): void;
}
