<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Commands;

use InvalidArgumentException;

/**
 * What {@see CommandActionTypeInterface::handle()} asks Core to do with the conversation. Core does it with the same
 * code as for its built-in command types, so a Solution never ends a session or starts a flow itself.
 *
 * @experimental since foundation 0.50
 */
final readonly class CommandOutcome
{
    /**
     * @param  string|null  $reply   text sent to the contact, or null for none
     * @param  string|null  $flowId  the flow to start, for {@see CommandOutcomeKind::StartFlow} only
     */
    private function __construct(
        public CommandOutcomeKind $kind,
        public ?string $reply = null,
        public ?string $flowId = null,
    ) {
    }

    /** Send a reply and leave the session alone; null sends nothing. */
    public static function reply(?string $text): self
    {
        return new self(CommandOutcomeKind::Reply, self::text($text));
    }

    /** Nothing to send, nothing to change. */
    public static function none(): self
    {
        return new self(CommandOutcomeKind::Reply);
    }

    /** End the contact's active session, then send the reply, if any. */
    public static function endSession(?string $reply = null): self
    {
        return new self(CommandOutcomeKind::EndSession, self::text($reply));
    }

    /**
     * End the contact's active session and start the flow (its latest active version in the tenant), then send the
     * reply, if any. A flow the tenant does not have starts nothing.
     */
    public static function startFlow(string $flowId, ?string $reply = null): self
    {
        if ('' === mb_trim($flowId)) {
            throw new InvalidArgumentException('A command outcome that starts a flow needs the flow id.');
        }

        return new self(CommandOutcomeKind::StartFlow, self::text($reply), $flowId);
    }

    private static function text(?string $text): ?string
    {
        return null === $text || '' === $text ? null : $text;
    }
}
