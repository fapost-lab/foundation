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
     * @param  string  $command      the command as typed and matched, such as "/feedback"
     * @param  string  $language     the contact's language, or the tenant's fallback language when the contact has none
     * @param  string  $channelType  the type of the channel the message came through, such as "telegram"
     */
    public function __construct(
        public string $command,
        public string $tenantId,
        public string $contactId,
        public string $assistantId,
        public string $channelType,
        public string $language,
    ) {
    }
}
