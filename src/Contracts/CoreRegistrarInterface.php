<?php

declare(strict_types=1);

namespace Fapost\Foundation\Contracts;

use Closure;
use Fapost\Foundation\Manifest\SolutionManifest;

/**
 * Single registration entry point for platform extensions.
 *
 * An extension declares intent through CoreRegistrar.
 * It must not call Route, Schedule, or Migrations directly.
 *
 * The implementation lives in Core.
 */
interface CoreRegistrarInterface
{
    /**
     * Register a NodeHandler for the flow engine.
     *
     * @param  class-string<NodeHandlerInterface>  $handlerClass
     */
    public function registerNodeHandler(string $handlerClass): void;

    /**
     * Register a DataAccessor for reads from condition nodes.
     *
     * @param  class-string<DataAccessorInterface>  $accessorClass
     * @param  string                               $namespace  For example: "hr", "recruitment"
     */
    public function registerDataAccessor(string $namespace, string $accessorClass): void;

    /**
     * Register a RAG adapter.
     *
     * @param  class-string<RagAdapterInterface>  $adapterClass
     * @param  string                             $provider  For example: "openai_assistants", "pgvector"
     */
    public function registerRagAdapter(string $provider, string $adapterClass): void;

    /**
     * Register extension manifest for boot-time validation
     * and platform:update lifecycle.
     */
    public function registerManifest(SolutionManifest $manifest): void;

    /**
     * Declare extension routes for platform orchestration.
     */
    public function registerRoutes(Closure $routes): void;

    /**
     * Declare extension schedule callbacks for platform orchestration.
     */
    public function registerSchedule(Closure $schedule): void;

    /**
     * Declare extension migrations path for platform orchestration.
     */
    public function registerMigrations(string $path): void;
}
