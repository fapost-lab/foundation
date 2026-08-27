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
 *       public function getId(): string { return 'viber'; }
 *       public function getVersion(): string { return '1.0.0'; }
 *
 *       public function getManifest(): SolutionManifest
 *       {
 *           return SolutionManifest::make(
 *               id: $this->getId(),
 *               version: $this->getVersion(),
 *               requiresPlatform: '>=1.0.0 <2.0.0',
 *               requiresCapabilities: ['messaging.channel_adapter'],
 *           );
 *       }
 *
 *       protected function registerExtensions(CoreRegistrarInterface $registrar): void
 *       {
 *           $registrar->registerChannelAdapter(ViberChannelAdapter::class);
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

        $registrar->registerManifest($this->getManifest());
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
