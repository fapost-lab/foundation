<?php

declare(strict_types=1);

namespace Fapost\Foundation\Lifecycle;

use Fapost\Foundation\Contracts\ActivatableInterface;
use Fapost\Foundation\Contracts\CoreRegistrarInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for Plugin packages.
 *
 * A Plugin extends platform capabilities without adding niche domain logic.
 * Examples: a new channel adapter (Viber), a new RAG provider (pgvector).
 *
 * Difference from a Solution:
 * - A Solution adds domain logic (HR, Recruitment)
 * - A Plugin adds platform capabilities (new channel, new RAG provider)
 *
 * Example usage in fapost/plugin-viber:
 *
 *   class ViberPluginServiceProvider extends AbstractPluginServiceProvider
 *   {
 *       protected function registerExtensions(CoreRegistrarInterface $registrar): void
 *       {
 *           $registrar->registerNodeHandler(ViberNodeHandler::class);
 *       }
 *   }
 */
abstract class AbstractPluginServiceProvider extends ServiceProvider implements ActivatableInterface
{
    /**
     * Register platform extensions through CoreRegistrar.
     */
    abstract protected function registerExtensions(CoreRegistrarInterface $registrar): void;
    public function onActivate(): void
    {
    }

    public function onDeactivate(): void
    {
    }

    public function register(): void
    {
        $this->registerBindings();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $registrar = $this->app->make(CoreRegistrarInterface::class);

        $this->registerExtensions($registrar);
        $this->bootPlugin();
    }

    protected function registerBindings(): void
    {
    }

    protected function bootPlugin(): void
    {
    }
}
