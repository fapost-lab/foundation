<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Commands;

/**
 * What a command does to the conversation. See {@see CommandOutcome}.
 *
 * @experimental since foundation 0.50
 */
enum CommandOutcomeKind: string
{
    /** Leave the session alone; send the reply, if any. */
    case Reply = 'reply';

    /** End the contact's active session, if any; then send the reply, if any. */
    case EndSession = 'end_session';

    /** End the contact's active session, if any, and start the flow; then send the reply, if any. */
    case StartFlow = 'start_flow';
}
