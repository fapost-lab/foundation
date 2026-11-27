<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Commands;

use Fapost\Foundation\Flow\DTO\NodeConfigIssue;

/**
 * A kind of action a global chat command can take, next to Core's built-in ones (`terminate_session`, `start_flow`,
 * `send_message`).
 *
 * A Solution registers it through {@see \Fapost\Foundation\Contracts\CoreRegistrarInterface::registerCommandActionType()}
 * and declares its {@see type()} in `extra.fapost.command_types`. A tenant then picks it for a command of an assistant
 * and fills the config {@see configSchema()} describes; a {@see SolutionCommandInterface} the Solution brings may use it
 * too. It runs only for a tenant that activated the Solution.
 *
 * A command runs before Core takes the session's lock, so the type never touches a session: {@see handle()} returns a
 * {@see CommandOutcome} and Core carries it out — ends the session, starts a flow, sends the reply.
 *
 * Every method but {@see handle()} must be pure and cheap: Core keeps the class and builds a fresh instance whenever it
 * asks one of these questions, outside any tenant's data.
 *
 * @experimental since foundation 0.50
 */
interface CommandActionTypeInterface
{
    /**
     * The type as an assistant's commands store it: "<solution id>." followed by a snake_case name, such as
     * "feedback.thank", at most 64 characters. Core's own types carry no dot. It never changes once a tenant uses it.
     */
    public function type(): string;

    /**
     * What a staff user reads in the list of command types, in English.
     */
    public function label(): string;

    /**
     * The config form of one command of this type, in the schema language of
     * {@see \Fapost\Foundation\Contracts\NodeHandlerInterface::configSchema()}. It shapes the form only:
     * {@see validateConfig()} is what checks a config.
     *
     * @return array<string, mixed>
     */
    public function configSchema(): array;

    /**
     * Problems of one command's config, checked when the assistant's commands are saved and when a Solution's
     * command is registered. An error refuses it; a warning is shown. An empty list means the config is fine.
     *
     * @param  array<string, mixed>  $config
     * @return list<NodeConfigIssue>
     */
    public function validateConfig(array $config): array;

    /**
     * Carry the command out for one contact, inside the tenant's context. It may read and write the Solution's own
     * data; what happens to the conversation is the outcome it returns. A command runs before the session's lock, so
     * it may run twice for one contact at once, or again when Core retries the incoming update: what it writes must be
     * safe to repeat ({@see CommandContext::$updateId} names the update). An exception is reported by Core; the
     * message counts as handled, never reaches a flow, and the contact gets no answer.
     *
     * @param  array<string, mixed>  $config  the command's config, as validated
     */
    public function handle(CommandContext $context, array $config): CommandOutcome;
}
