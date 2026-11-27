<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Commands;

/**
 * Who typed a command, and where: what {@see CommandActionTypeInterface::handle()} is given.
 *
 * @experimental since foundation 0.50
 */
final readonly class CommandContext
{
    /**
     * @param  string       $command      the command as typed and matched, such as "/feedback"
     * @param  string       $tenantId     the tenant the command runs for; the run is already inside its context
     * @param  string       $contactId    the contact who typed it
     * @param  string       $assistantId  the assistant whose channel it came through
     * @param  string       $channelType  the type of the channel the message came through, such as "telegram"
     * @param  string       $language     the contact's language, or the tenant's fallback language when the contact has none
     * @param  string|null  $updateId     the provider's id of the incoming update ({@see \Fapost\Foundation\DTO\IncomingMessage::$updateId}),
     *                                    the same on a retry: a key for writes that must happen once; null when Core has none
     */
    public function __construct(
        public string $command,
        public string $tenantId,
        public string $contactId,
        public string $assistantId,
        public string $channelType,
        public string $language,
        public ?string $updateId = null,
    ) {
    }
}
