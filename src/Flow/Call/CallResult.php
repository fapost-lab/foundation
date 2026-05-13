<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Call;

/**
 * Outcome of a single transport invocation.
 *
 * Successful or not — both shapes return a populated DTO; transports never
 * throw to indicate business errors (only programmer-visible bugs warrant
 * exceptions). The call node uses {@code success} to pick the success/error
 * output handle; {@code payload} feeds {@code result_mapping}; {@code metadata}
 * carries transport-specific extras (HTTP status_code, duration_ms, …).
 *
 * Per ADR Message Routing § HTTP non-2xx — for HTTP transport the
 * success/error split is governed by the {@code success_when} option.
 */
final readonly class CallResult
{
    public function __construct(
        public bool $success,
        public mixed $payload = null,
        public ?string $errorCode = null,
        /** @var array<string, mixed> */
        public array $metadata = [],
    )
    {
    }

    public static function ok(mixed $payload = null, array $metadata = []): self
    {
        return new self(true, $payload, null, $metadata);
    }

    public static function error(string $errorCode, mixed $payload = null, array $metadata = []): self
    {
        return new self(false, $payload, $errorCode, $metadata);
    }
}
