<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

use FAPost\Foundation\Flow\Contracts\ContactWriterInterface;
use FAPost\Foundation\Flow\Contracts\ExpressionEngineInterface;
use FAPost\Foundation\Flow\Contracts\ScopedStateReaderInterface;

/**
 * Node execution context passed by the engine to each handler.
 * Immutable. A handler may read it but must not mutate it.
 *
 * Identifier fields and {@see $incoming} are populated for every execution.
 * The three service fields ({@see $stateReader}, {@see $contactWriter},
 * {@see $expressionEngine}) are populated by the engine starting from Phase B-1
 * and may be {@code null} only in legacy unit tests that build the context
 * directly without passing through the engine.
 *
 * Reconciliation note (D-2/D-6): foundation domain interfaces (Contact, Session,
 * Definition, Tenant) are intentionally absent — handlers that need full
 * objects inject repositories via constructor.
 */
final readonly class NodeExecutionContext
{
    public function __construct(
        /** Tenant ID (ULID as RFC4122 UUID string) */
        public string $tenantId,

        /** Contact ID */
        public string $contactId,

        /** Flow session ID */
        public string $sessionId,

        /** Node ID in flow definition */
        public string $nodeId,

        /** Idempotency key for external calls from handler */
        public string $idempotencyKey,

        /** Platform/channel: telegram, whatsapp, ... */
        public string $platform,

        /** Runtime resolved content language */
        public string $resolvedLanguage = 'en',

        /** Present on {@code resume()} for the first node execution in that run only. */
        public ?IncomingMessage $incoming = null,

        /** Unified read surface across contact / session-state / module namespaces. */
        public ?ScopedStateReaderInterface $stateReader = null,

        /** Immediate writer for {@code contact.*} mutations (replaces legacy effects[]). */
        public ?ContactWriterInterface $contactWriter = null,

        /** Engine resolved from the running flow_definition's expression_engine snapshot. */
        public ?ExpressionEngineInterface $expressionEngine = null,
    ) {
    }
}
