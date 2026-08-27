<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

/**
 * Immediate writer for contact-scoped mutations from inside a node handler.
 *
 * Replaces the legacy {@code effects[]} array on NodeExecutionResult. A handler
 * that needs to mutate a contact (set an attribute, change canonical language,
 * etc.) calls this writer directly during {@code execute()}; the engine no
 * longer interprets effect descriptors.
 *
 * Each write is committed immediately in its own DB transaction. This is by
 * design — the contact lives outside the session JSON and must remain consistent
 * even if the session save fails afterwards (engine retry will re-apply the
 * write idempotently).
 *
 * Path rules:
 *  - Path MUST start with "contact." prefix
 *  - One level of grouping allowed: "contact.&lt;group&gt;.&lt;field&gt;"
 *  - Reserved keys (id, tenant_id, channel_id, channel, meta.*) throw {@see \LogicException}
 *  - Unknown intermediate object segments are created on the way (JSONB)
 *  - Type mismatch with a canonical column ends the session (handler is unsafe)
 *
 * Auto-instrumentation: every successful write emits a state_change event into
 * flow_session_history when the running flow_definition has logging_enabled.
 * Handlers do not call any logger — instrumentation is centralised.
 */
interface ContactWriterInterface
{
    /**
     * Apply a write to a contact-scoped path.
     *
     * @param  string  $path   fully-qualified path starting with "contact."
     * @param  mixed   $value  scalar, array, or null — must be JSON-serialisable
     */
    public function write(string $path, mixed $value): void;
}
