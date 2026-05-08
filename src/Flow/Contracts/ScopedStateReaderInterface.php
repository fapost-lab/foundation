<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Contracts;

/**
 * Read-only access to namespaced session/contact/module state during node execution.
 *
 * Provides a unified read API across all state namespaces:
 *  - contact.*   resolves through the Contact model (canonical columns + attributes JSONB)
 *  - flow.*      reads from session.state.flow
 *  - rag.*       reads from session.state.rag
 *  - call.*      reads from session.state.call
 *  - system.*    reads from session.state.system
 *  - module.*    resolves through the registered DataAccessor for that module
 *
 * Implementations return {@code null} for unknown paths or when intermediate
 * segments are not objects. They never throw on missing paths.
 *
 * Handlers receive an instance via {@see \FAPost\Foundation\DTO\NodeExecutionContext}.
 */
interface ScopedStateReaderInterface
{
    /**
     * Resolve a fully-qualified state path to its current value.
     *
     * Examples:
     *  - "contact.first_name"
     *  - "flow.attempts"
     *  - "module.hr.department"
     *  - "system.language"
     */
    public function read(string $path): mixed;
}
