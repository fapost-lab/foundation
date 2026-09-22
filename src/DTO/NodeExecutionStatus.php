<?php

declare(strict_types=1);

namespace Fapost\Foundation\DTO;

enum NodeExecutionStatus: string
{
    /** Node finished; follow {@see NodeExecutionResult::$sourceHandle} through flow edges when set. */
    case Executed = 'executed';

    /** Waiting for user input (e.g. input node). */
    case Waiting = 'waiting';

    /** Node paused; the engine runs it again at its resume time, or with the next message ({@see NodeExecutionResult::delayed()}). */
    case Delayed = 'delayed';

    /** Handler or engine error; session should be marked failed. */
    case Failed = 'failed';

    /** Flow ended successfully (e.g. end node). */
    case Finished = 'finished';
}
