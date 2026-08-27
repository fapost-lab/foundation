<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Call;

/**
 * Per-call context surface available to transports.
 *
 * String IDs only (consistent with NodeExecutionContext) — transports never
 * touch domain models directly. {@see $idempotencyKey} is the
 * {@code session_id:node_id:attempt_number} composite from the engine; HTTP
 * transports forward it as the `Idempotency-Key` header. In V1 attempt_number
 * is statically 1 — full Redis SET NX dedup is V1.x.
 */
final readonly class CallContext
{
    public function __construct(
        public string $tenantId,
        public string $contactId,
        public string $sessionId,
        public string $nodeId,
        public string $idempotencyKey,
    )
    {
    }
}
