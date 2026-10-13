<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Call;

/**
 * Per-call context surface available to transports.
 *
 * String IDs only (consistent with NodeExecutionContext) — transports never
 * touch domain models directly. {@see $idempotencyKey} is the engine's
 * execution key, a ':' and the node id. The execution key is new on each pass
 * through a node (a loop gives each pass its own key) and the same when a queue
 * retry runs the pass again, so a retry repeats the key and a loop pass does
 * not. HTTP transports forward it as the `Idempotency-Key` header.
 */
final readonly class CallContext
{
    public function __construct(
        public string $tenantId,
        public string $contactId,
        public string $sessionId,
        public string $nodeId,
        public string $idempotencyKey,
    ) {
    }
}
