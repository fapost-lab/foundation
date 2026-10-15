<?php

declare(strict_types=1);

namespace Fapost\Foundation\Lifecycle;

use Fapost\Foundation\Contracts\ActivatableInterface;
use Fapost\Foundation\Contracts\CoreRegistrarInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for Solution packages.
 *
 * A Solution is an external composer package with niche functionality
 * (HR, Recruitment, etc.). It never lives in app/Solutions/.
 *
 * The Solution's identity is not declared here but in its composer.json
 * (`type: fapost-solution` and the `extra.fapost` block, see
 * {@see \Fapost\Foundation\Solution\Manifest\ManifestSchema}); Core boots the provider named there.
 *
 * Example usage in a Solution package:
 *
 *   class HrSolutionServiceProvider extends AbstractSolutionServiceProvider
 *   {
 *       protected function registerExtensions(CoreRegistrarInterface $registrar): void
 *       {
 *           $registrar->registerActionHandler(CreateAssessmentAction::class);
 *       }
 *   }
 */
abstract class AbstractSolutionServiceProvider extends ServiceProvider implements ActivatableInterface
{
    /**
     * Register all extensions through CoreRegistrar.
     * Called by Core through {@see registerExtensionsWith()}.
     */
    abstract protected function registerExtensions(CoreRegistrarInterface $registrar): void;

    /**
     * Entry point for Core: hands this Solution its own registrar.
     *
     * Core calls it once per process, after every provider's register() has run and before any
     * boot(), so extensions are registered before bootSolution() and before Core's registries
     * freeze. The registrar is created per Solution so Core can check what it registers against
     * the Solution's manifest; the provider never pulls one from the container.
     */
    final public function registerExtensionsWith(CoreRegistrarInterface $registrar): void
    {
        $this->registerExtensions($registrar);
    }

    /**
     * Does nothing by default. Override for custom activation logic.
     */
    public function onActivate(): void
    {
    }

    /**
     * Does nothing by default. Override for graceful deactivation.
     */
    public function onDeactivate(): void
    {
    }

    /**
     * register() should only register container bindings.
     * Do not call CoreRegistrar here because it may not be ready yet.
     */
    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * boot() runs bootSolution(). Extensions are not registered here: Core has already called
     * {@see registerExtensionsWith()} before the first boot().
     */
    public function boot(): void
    {
        $this->bootSolution();
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
     * Called after registerExtensionsWith() has run.
     */
    protected function bootSolution(): void
    {
    }

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
     * Declare package migrations path.
     *
     * @param  string  $path  Absolute path to the migrations directory
     */
    protected function declareSolutionMigrations(string $path): void
    {
        // TODO delegate to CoreRegistrar when migration orchestration is implemented
    }
}
