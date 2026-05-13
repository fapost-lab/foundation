<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Call;

/**
 * Resolved request payload handed to a {@see CallTransportInterface}.
 *
 * Already produced by the call node handler — expressions in target/parameters
 * are evaluated, transport_options passed through verbatim. Transports treat
 * this DTO as immutable input.
 */
final readonly class CallRequest
{
    public function __construct(
        /** Transport-specific target. For http: "POST https://...". For handler: action id. */
        public string $target,
        /** @var array<string, mixed> */
        public array $parameters = [],
        /** @var array<string, mixed> Transport-specific options (timeout, headers, success_when, …) */
        public array $options = [],
    )
    {
    }
}
