<?php

declare(strict_types=1);

namespace Fapost\Foundation\Action;

use Fapost\Foundation\Flow\Call\CallContext;

/**
 * Contract for in-process action handlers dispatched via the call node's
 * {@code handler} transport.
 *
 * Distinct from {@see \Fapost\Foundation\Contracts\NodeHandlerInterface}
 * (which executes nodes inside the flow graph). Action handlers are invoked
 * synchronously by {@code call} nodes targeting them; they receive resolved
 * parameters and return a result placed into {@code call.payload} for
 * downstream {@code result_mapping}.
 *
 * Namespacing convention: domain prefix in id (e.g. "crm.sync_contact",
 * "hr.create_assessment"). Reserved namespaces: "core.*", "platform.*".
 */
interface ActionHandlerInterface
{
    /**
     * Stable identifier in the ActionHandlerRegistry. Conflicts cause boot
     * failure — operator-visible failure beats silent override.
     */
    public function id(): string;

    /**
     * Handler version. Bump on breaking changes to parameters/return shape.
     */
    public function version(): int;

    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $parameters  resolved parameters from call node
     * @return mixed                              JSON-serialisable; placed into CallResult.payload
     */
    public function handle(array $parameters, CallContext $context): mixed;
}
