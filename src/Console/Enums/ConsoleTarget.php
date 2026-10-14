<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Enums;

/**
 * Where a context action appears. The value is what the console client and the documentation use.
 *
 * New targets are added as new cases in a minor release; callers matching on it keep a default arm.
 *
 * @experimental
 */
enum ConsoleTarget: string
{
    /** The actions menu of a contact's page. */
    case ContactView = 'contact.view';

    /** The row actions of the contacts table. */
    case ContactRow = 'contact.row';

    /** The actions menu of a conversation's page. */
    case ConversationView = 'conversation.view';
}
