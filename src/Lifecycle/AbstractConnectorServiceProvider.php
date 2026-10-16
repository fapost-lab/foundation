<?php

declare(strict_types=1);

namespace Fapost\Foundation\Lifecycle;

use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for connector packages.
 *
 * A connector is a deploy-time Composer package of type `fapost-connector` that brings a messaging
 * channel. Core boots it for the whole installation: unlike a Solution it has no per-tenant
 * activation, so there is no activation interface to implement.
 *
 * The connector's identity is not declared here but in its composer.json (`type: fapost-connector`
 * and the `extra.fapost` block, see {@see \Fapost\Foundation\Connector\Manifest\ConnectorManifestSchema});
 * Core boots the provider named there. Registering the channels the connector owns arrives in a
 * later Foundation release.
 *
 * Since foundation 0.21, experimental.
 */
abstract class AbstractConnectorServiceProvider extends ServiceProvider
{
    /**
     * register() should only register container bindings.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * boot() runs bootConnector().
     */
    public function boot(): void
    {
        $this->bootConnector();
    }

    /**
     * Override for container bindings registration.
     * Called in register().
     */
    protected function registerBindings(): void
    {
    }

    /**
     * Override for additional boot actions (migrations, configs, routes).
     */
    protected function bootConnector(): void
    {
    }
}
