<?php

declare(strict_types=1);

namespace Fapost\Foundation\Contracts;

use Closure;
use Fapost\Foundation\Action\ActionHandlerInterface;

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
     * Register an action handler for the call node's {@code handler} transport.
     *
     * By class, not by instance: Core builds the handler on each call, in the scope of the
     * running job, so its constructor may depend on scoped services. Core also builds one
     * throwaway instance at registration to read {@see ActionHandlerInterface::id()}, so the
     * constructor must be cheap and must not touch tenant data.
     *
     * A registrar created for a Solution accepts only action ids that the Solution declares in
     * `extra.fapost.actions`, and fails registration for any other id or a duplicate.
     *
     * @param  class-string<ActionHandlerInterface>  $handlerClass
     */
    public function registerActionHandler(string $handlerClass): void;

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
