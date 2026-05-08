<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\History;

/**
 * Vocabulary of structured history events written into flow_session_history.
 *
 * Stable across versions — plugins/analytics tooling depend on these values
 * to filter history rows. Adding a new event type requires bumping the
 * V1 history schema contract, never repurposing existing values.
 */
enum HistoryEventType: string
{
    /** A namespaced state path was mutated (path, old_value, new_value). */
    case StateChange = 'state_change';

    /** Engine entered a node (lifecycle marker). */
    case NodeEntered = 'node_entered';

    /** Handler raised or session was failed at this node. */
    case NodeFailed = 'node_failed';

    /** Parent recorded a child subflow session creation. */
    case SubflowStarted = 'subflow_started';

    /** Parent recorded a child subflow session end with outcome. */
    case SubflowReturned = 'subflow_returned';
}
