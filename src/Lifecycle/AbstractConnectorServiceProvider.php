<?php

declare(strict_types=1);

namespace Fapost\Foundation\Lifecycle;

use Fapost\Foundation\Connector\ConnectorRegistrarInterface;
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
 * Core boots the provider named there. The channels the connector owns are registered in
 * registerChannels(), which Core reaches through {@see registerChannelsWith()}.
 *
 * Since foundation 0.21, experimental; channel registration since 0.23.
 */
abstract class AbstractConnectorServiceProvider extends ServiceProvider
{
    /**
     * Register every channel this connector owns through the registrar.
     * Called by Core through {@see registerChannelsWith()}.
     */
    abstract protected function registerChannels(ConnectorRegistrarInterface $registrar): void;

    /**
     * Entry point for Core: hands this connector its own registrar.
     *
     * Core calls it once per process, after every provider's register() has run and before any
     * boot(), so channels are registered before bootConnector() and before Core's registries
     * freeze. The registrar is created per connector so Core can check what it registers against
     * the connector's manifest; the provider never pulls one from the container.
     */
    final public function registerChannelsWith(ConnectorRegistrarInterface $registrar): void
    {
        $this->registerChannels($registrar);
    }

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
