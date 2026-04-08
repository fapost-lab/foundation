<?php

declare(strict_types=1);

namespace FAPost\Foundation\Contracts;

use FAPost\Foundation\DTO\NodeExecutionContext;
use FAPost\Foundation\DTO\NodeExecutionResult;

/**
 * Flow engine node handler contract.
 *
 * Handler versioning rules:
 * - backward-compatible change (new field with default) keeps version() unchanged
 * - breaking change requires version()++, while old handlers remain registered
 * - remove a handler only when no active flow references it
 *
 * The engine resolves handler by (type, version) from an in-memory registry, without DB queries.
 */
interface NodeHandlerInterface
{
    /**
     * Node type. For example: "send_message", "sync_employee".
     * Must be unique among all registered handlers.
     */
    public function type(): string;

    /**
     * Current handler version.
     */
    public function version(): int;

    /**
     * List of node versions supported by this handler.
     * Usually [currentVersion] or [currentVersion, previousVersion] for backward compatibility.
     *
     * @return int[]
     */
    public function supportedVersions(): array;

    /**
     * Execute node.
     *
     * @param  array<string, mixed>  $nodeConfig  Node config from flow definition JSON
     * @param  array<string, mixed>  $state       Namespaced session state (read-only for handler)
     * @param  NodeExecutionContext  $context     Tenant, contact, and session metadata
     */
    public function execute(
        array $nodeConfig,
        array $state,
        NodeExecutionContext $context,
    ): NodeExecutionResult;
}
