<?php

declare(strict_types=1);

namespace Fapost\Foundation\Messaging;

/**
 * Optional capability for outbound providers that can show a "typing"-style
 * processing indicator to the user during long-running flow execution.
 *
 * Implemented by providers (e.g. Telegram via {@code sendChatAction(typing)})
 * alongside the mandatory {@see ProviderSenderInterface}. The Core
 * TypingIndicatorService discovers capable providers via {@code instanceof}
 * and routes start/stop/refresh through them; non-capable providers are
 * silently skipped.
 *
 * Indicator lifecycle (per ADR Message Routing & Concurrency Control):
 *  - Started when the message router takes a job from the queue
 *  - Refreshed before long-running nodes (call, rag_query) and on lock
 *    heartbeat tick (typically every 4 s; below Telegram's 5 s TTL)
 *  - Stopped when the session reaches waiting_input or end
 *
 * Implementations must be no-throw — failure to update an indicator is
 * never fatal; callers log and continue.
 */
interface TypingCapableProviderInterface
{
    /**
     * Show the indicator. Returns an opaque handle for follow-up calls.
     */
    public function indicateProcessing(string $chatId, string $transportToken): ProcessingIndicatorHandle;

    /**
     * Refresh / extend the indicator (best-effort). For Telegram this is just
     * another sendChatAction call — re-initiates the 5-second timer.
     */
    public function refreshProcessing(ProcessingIndicatorHandle $handle, string $transportToken): void;

    /**
     * Stop the indicator (best-effort). For providers where the indicator
     * auto-clears (Telegram), this is typically a no-op.
     */
    public function stopProcessing(ProcessingIndicatorHandle $handle, string $transportToken): void;
}
