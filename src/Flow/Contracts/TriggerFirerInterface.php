<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use InvalidArgumentException;

/**
 * Starts the flows that listen to a registered trigger type ({@see TriggerTypeInterface}).
 *
 * The implementation lives in Core; an extension takes it as a constructor dependency of the code that notices the
 * fact (an action handler, a node, a queued job of its own). A fire is fire-and-forget: Core queues the fan-out after
 * the caller's database transaction commits and returns at once, so the caller learns nothing about the runs. For
 * every active trigger of the type in the tenant whose config {@see TriggerTypeInterface::matches()} the payload,
 * Core starts one contactless run, if the type's Solution is active for the tenant; otherwise nothing starts.
 *
 * A fire raised while a flow runs is one level deeper in the chain of flows setting each other off, and past Core's
 * limit (five) it starts nothing.
 *
 * @experimental
 */
interface TriggerFirerInterface
{
    /** The largest payload, in bytes of its JSON encoding, that {@see fire()} accepts. */
    public const int MAX_PAYLOAD_BYTES = 65536;

    /**
     * @param  string  $tenantId  the tenant whose flows start; always explicit, never read from the current context
     * @param  string  $type      a registered trigger type, such as "feedback.submitted"
     * @param  string  $fireKey   what makes this fire unique within the type, such as the id of the submitted form
     *                            entry: firing the same key again starts no second run of a trigger
     * @param  array<string, mixed>  $payload  JSON-serializable data the runs read as `flow.trigger.*`
     *
     * @throws InvalidArgumentException when the type is not registered, the fire key is empty, or the payload is not
     *                                  JSON-serializable or larger than {@see MAX_PAYLOAD_BYTES}
     */
    public function fire(string $tenantId, string $type, string $fireKey, array $payload): void;
}
