<?php

declare(strict_types=1);

namespace FAPost\Foundation\DTO;

/**
 * Node execution context passed by the engine to each handler.
 * Immutable. A handler may read it but must not mutate it.
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
    ) {
    }
}
