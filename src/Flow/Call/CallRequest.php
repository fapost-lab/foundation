<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Call;

use Fapost\Foundation\Credentials\DTO\CredentialSecret;

/**
 * Resolved request payload handed to a {@see CallTransportInterface}.
 *
 * Already produced by the call node handler — expressions in target/parameters
 * are evaluated, transport_options passed through verbatim. Transports treat
 * this DTO as immutable input.
 *
 * `credential` is the saved credential the node chose, resolved for this call: a transport that supports
 * credentials applies it and keeps its values out of `parameters`, `options` and the result. A request that
 * holds one cannot be serialized ({@see CredentialSecret}), so it never reaches a queue or a cache; one
 * without a credential serializes as before.
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
        /** @experimental The saved credential to authorize the call with; null for none. */
        public ?CredentialSecret $credential = null,
    ) {
    }
}
