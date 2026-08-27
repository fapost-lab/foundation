<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Call;

/**
 * Contract for a {@code call}-node transport.
 *
 * Built-in transports in V1: {@code http} (HTTP request) and {@code handler}
 * (in-process ActionHandler dispatch). Plugins/Solutions register additional
 * transports via CoreRegistrar; conflicts on id() cause boot failure.
 */
interface CallTransportInterface
{
    /**
     * Stable transport identifier in the registry. Examples: "http", "handler",
     * "graphql", "grpc". Plugins should namespace their ids.
     */
    public function id(): string;

    /**
     * Execute one call. Implementations MUST NOT throw for business-level
     * failures — return a {@see CallResult::error()} with a meaningful
     * {@code errorCode} instead. Throw only for genuine programmer errors
     * (misconfigured registry, contract violations).
     */
    public function execute(CallRequest $request, CallContext $context): CallResult;
}
