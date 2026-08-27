<?php

declare(strict_types=1);

namespace Fapost\Foundation\Analytics\Enums;

enum AnalyticsEventType: string
{
    case FlowStarted   = 'flow_started';
    case FlowCompleted = 'flow_completed';
    case FlowCancelled = 'flow_cancelled';
    case FlowFailed    = 'flow_failed';
    case RagQueryMade  = 'rag_query_made';
    case BroadcastSent = 'broadcast_sent';
}
