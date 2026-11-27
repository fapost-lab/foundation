<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Commands;

/**
 * A global chat command a Solution brings ready-made, such as "/feedback": a contact who types it, in a tenant that
 * activated the Solution, gets the action {@see actionType()} names with {@see config()}.
 *
 * A Solution registers it through {@see \Fapost\Foundation\Contracts\CoreRegistrarInterface::registerCommand()} and
 * declares its {@see command()} in `extra.fapost.commands`. Commands are matched in this order: the tenant's own
 * commands of the assistant, then a Solution's, then Core's built-in ones. A Solution never takes a built-in command
 * (see {@see \Fapost\Foundation\Solution\Manifest\ManifestSchema::RESERVED_COMMANDS}), so the order only lets a
 * tenant shadow a Solution's command with one of its own.
 *
 * Every method must be pure and cheap: Core builds a fresh instance whenever it reads one.
 *
 * @experimental since foundation 0.50
 */
interface SolutionCommandInterface
{
    /**
     * The command as a contact types it: "/" followed by 1 to 32 lowercase letters, digits or underscores (the rule of
     * Telegram's bot commands, the strictest of the channels). Matched by exact equality.
     */
    public function command(): string;

    /**
     * The action it takes: a type of the same Solution's {@see CommandActionTypeInterface}, or Core's built-in
     * `send_message` or `terminate_session`. Not the built-in `start_flow`, whose config names a flow of one tenant;
     * a Solution's own type may still start a flow, from its config, by returning {@see CommandOutcome::startFlow()}.
     */
    public function actionType(): string;

    /**
     * The action's config. For `send_message` the key `text`, for `terminate_session` the optional key `response`:
     * a string, or a map of language code to string.
     *
     * @return array<string, mixed>
     */
    public function config(): array;

    /**
     * What a staff user reads about the command, in English.
     */
    public function label(): string;
}
