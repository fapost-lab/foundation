<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

use Fapost\Foundation\Flow\DTO\NodeConfigIssue;

/**
 * A kind of flow trigger an extension adds next to Core's built-in ones (message, schedule, webhook, api, event).
 *
 * A Solution registers it through {@see \Fapost\Foundation\Contracts\CoreRegistrarInterface::registerTriggerType()}
 * and declares its {@see type()} in `extra.fapost.triggers`. A flow author picks it in the builder and fills the form
 * {@see configSchema()} describes; the Solution's own code then starts runs with
 * {@see TriggerFirerInterface::fire()}, and Core starts one run for every active trigger of the type whose config
 * {@see matches()} the payload.
 *
 * A run a registered trigger starts has no contact: a flow with such a trigger may hold no node that needs one. The
 * payload is the run's `flow.trigger.*` state, and `flow.trigger_type` is the type.
 *
 * Every method must be pure — no I/O, no state, the same input always gives the same answer — and cheap: Core keeps
 * the class and builds a fresh instance whenever it asks one of these questions, outside any tenant's data.
 *
 * @experimental
 */
interface TriggerTypeInterface
{
    /**
     * The type as flows store it: "<solution id>." followed by a snake_case name, such as "feedback.submitted", at
     * most 64 characters. Core's own types carry no dot. It never changes once a flow uses it.
     */
    public function type(): string;

    /**
     * What the flow author reads in the builder's trigger list, in English.
     */
    public function label(): string;

    /**
     * The trigger's config form, in the schema language of
     * {@see \Fapost\Foundation\Contracts\NodeHandlerInterface::configSchema()}, which the builder renders; extensions
     * ship no builder components of their own. The schema shapes the form only: {@see validateConfig()} is what checks
     * a config.
     *
     * @return array<string, mixed>
     */
    public function configSchema(): array;

    /**
     * Problems of one trigger's config, checked when its flow is validated and published. An error blocks the
     * publish; a warning is shown to the author. An empty list means the config is fine.
     *
     * @param  array<string, mixed>  $config
     * @return list<NodeConfigIssue>
     */
    public function validateConfig(array $config): array;

    /**
     * Whether one fire of the type starts the trigger configured with `$config`, such as
     * `$config['form_id'] === $payload['form_id']`. Asked for every active trigger of the type in the tenant, so a
     * trigger that should start on every fire returns true.
     *
     * @param  array<string, mixed>  $config   the trigger's config, as validated at publish
     * @param  array<string, mixed>  $payload  what {@see TriggerFirerInterface::fire()} was given
     */
    public function matches(array $config, array $payload): bool;

    /**
     * Top-level keys of the payload, offered to the flow author as `flow.trigger.<key>` in expression pickers.
     *
     * @return list<string>
     */
    public function payloadFields(): array;
}
