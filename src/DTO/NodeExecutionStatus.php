<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

enum NodeExecutionStatus: string
{
    /** Node finished; follow {@see NodeExecutionResult::$sourceHandle} through flow edges when set. */
    case Executed = 'executed';

    /** Waiting for user input (e.g. input node). */
    case Waiting = 'waiting';

    /** Execution paused for a delay; resumed externally. */
    case Delayed = 'delayed';

    /** Handler or engine error; session should be marked failed. */
    case Failed = 'failed';

    /** Flow ended successfully (e.g. end node). */
    case Finished = 'finished';
}
