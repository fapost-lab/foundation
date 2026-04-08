<?php

declare(strict_types=1);

namespace FAPost\Foundation\Lifecycle;

use FAPost\Foundation\Contracts\ActivatableInterface;
use FAPost\Foundation\Contracts\CoreRegistrarInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for Solution packages.
 *
 * A Solution is an external composer package with niche functionality
 * (HR, Recruitment, etc.). It never lives in app/Solutions/.
 *
 * Example usage in fapost/solution-hr:
 *
 *   class HrSolutionServiceProvider extends AbstractSolutionServiceProvider
 *   {
 *       public function getId(): string { return 'hr'; }
 *       public function getVersion(): string { return '1.0.0'; }
 *
 *       public function getManifest(): SolutionManifest
 *       {
 *           return SolutionManifest::make(
 *               id: $this->getId(),
 *               version: $this->getVersion(),
 *               requiresPlatform: '>=1.0.0 <2.0.0',
 *               requiresCapabilities: ['flow.node_registry', 'flow.data_accessor'],
 *           );
 *       }
 *
 *       protected function registerExtensions(CoreRegistrarInterface $registrar): void
 *       {
 *           $registrar->registerNodeHandler(SyncEmployeeHandler::class);
 *           $registrar->registerDataAccessor('hr', HrDataAccessor::class);
 *       }
 *   }
 */
abstract class AbstractSolutionServiceProvider extends ServiceProvider implements ActivatableInterface
{
    /**
     * Does nothing by default. Override for custom activation logic.
     */
    public function onActivate(): void { }

    /**
     * Does nothing by default. Override for graceful deactivation.
     */
    public function onDeactivate(): void { }

    /**
     * register() should only register container bindings.
     * Do not call CoreRegistrar here because it may not be ready yet.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * Override for container bindings registration.
     * Called in register().
     */
    protected function registerBindings(): void { }

    /**
     * boot() registers extensions through CoreRegistrar.
     * Called after all service providers are registered.
     */
    public function boot(): void
    {
        if ( ! $this->app->bound(CoreRegistrarInterface::class)) {
            return;
        }

        $registrar = $this->app->make(CoreRegistrarInterface::class);

        $registrar->registerManifest($this->getManifest());
        $this->registerExtensions($registrar);
        $this->bootSolution();
    }

    /**
     * Register all extensions through CoreRegistrar.
     * Called in boot() after manifest validation.
     */
    abstract protected function registerExtensions(CoreRegistrarInterface $registrar): void;

    /**
     * Override for additional boot actions (migrations, configs, routes).
     * Called after registerExtensions().
     */
    protected function bootSolution(): void { }

    /**
     * Publish package config.
     *
     * @param  string  $from  Absolute path to the package config file
     * @param  string  $key   Config key (for example: 'fapost-hr')
     */
    protected function publishConfig(string $from, string $key): void
    {
        $this->publishes(
            [$from => config_path("{$key}.php")],
            "{$key}-config",
        );

        $this->mergeConfigFrom($from, $key);
    }

    /**
     * Register package migrations path.
     * Migrations are expected to run through platform:update, not direct migrate.
     *
     * @deprecated Use CoreRegistrar::registerMigrations() when CoreRegistrar migration orchestration is available.
     *
     * @param  string  $path  Absolute path to the migrations directory
     */
    protected function loadSolutionMigrations(string $path): void
    {
        trigger_error(
            'loadSolutionMigrations() is deprecated. Use CoreRegistrar::registerMigrations() when migration orchestration is available.',
            E_USER_DEPRECATED,
        );

        $this->declareSolutionMigrations($path);
    }

    /**
     * Declare package migrations path.
     *
     * @param  string  $path  Absolute path to the migrations directory
     */
    protected function declareSolutionMigrations(string $path): void
    {
        // TODO delegate to CoreRegistrar when migration orchestration is implemented
    }
}
