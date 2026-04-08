<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

enum NodeExecutionStatus: string
{
    /** Move to the next node (nextNodeId is required) */
    case Transition = 'transition';

    /** Wait for user input (input node) */
    case Waiting = 'waiting';

    /** Flow is completed */
    case Completed = 'completed';

    /** Handler failed: session is marked failed and fallback is sent */
    case Failed = 'failed';
}
