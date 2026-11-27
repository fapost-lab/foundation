<?php

declare(strict_types=1);

namespace Fapost\Foundation\Contracts;

use Closure;
use Fapost\Foundation\Action\ActionHandlerInterface;
use Fapost\Foundation\Flow\Commands\CommandActionTypeInterface;
use Fapost\Foundation\Flow\Commands\SolutionCommandInterface;
use Fapost\Foundation\Flow\Contracts\TriggerTypeInterface;

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
     * For a Solution, the handler's type() must be declared in `extra.fapost.nodes`, and its
     * category() must be a {@see \Fapost\Foundation\Flow\Enums\NodeCategory} value or the Solution's id;
     * a violation fails the boot.
     *
     * @param  class-string<NodeHandlerInterface>  $handlerClass
     */
    public function registerNodeHandler(string $handlerClass): void;

    /**
     * Register a flow trigger type.
     *
     * By class, like a node handler: Core builds a fresh instance whenever it asks the type something, and one
     * throwaway instance at registration to read {@see TriggerTypeInterface::type()}, so the constructor must be
     * cheap and must not touch tenant data. For a Solution, the type must be declared in `extra.fapost.triggers`; a
     * violation or a duplicate fails the boot.
     *
     * @param  class-string<TriggerTypeInterface>  $triggerTypeClass
     *
     * @experimental since foundation 0.46
     */
    public function registerTriggerType(string $triggerTypeClass): void;

    /**
     * Register a kind of action a global chat command can take.
     *
     * By class, like a trigger type: Core builds a fresh instance whenever it asks the type something, and one
     * throwaway instance at registration to read {@see CommandActionTypeInterface::type()}, so the constructor must
     * be cheap and must not touch tenant data. For a Solution, the type must be declared in
     * `extra.fapost.command_types`; a violation or a duplicate fails the boot.
     *
     * @param  class-string<CommandActionTypeInterface>  $commandActionTypeClass
     *
     * @experimental since foundation 0.50
     */
    public function registerCommandActionType(string $commandActionTypeClass): void;

    /**
     * Register a ready-made global chat command, such as "/feedback".
     *
     * By class: Core builds a fresh instance whenever it reads the command. For a Solution, the command must be
     * declared in `extra.fapost.commands`, and its action type must be one of the same Solution's command types or
     * Core's built-in `send_message` or `terminate_session`; a violation, or the same command registered twice by
     * one Solution, fails the boot. Two installed Solutions that declare one command are both refused by Core when it
     * reads their manifests, as two that claim one id are. A command Core itself takes as built-in in a later release
     * is ignored and reported, never fatal.
     *
     * @param  class-string<SolutionCommandInterface>  $commandClass
     *
     * @experimental since foundation 0.50
     */
    public function registerCommand(string $commandClass): void;

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
     *
     * Solutions do not use this: they declare their migrations in the manifest (`extra.fapost.migrations`).
     */
    public function registerMigrations(string $path): void;
}
