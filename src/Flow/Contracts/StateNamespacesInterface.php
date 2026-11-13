<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Contracts;

/**
 * Read-only view of the flow state namespaces known at runtime: the built-in ones
 * ({@see \Fapost\Foundation\Flow\Enums\StateNamespace}) and one per Solution that declares
 * `state: true` in its manifest, named by the Solution's id. Implemented by Core.
 *
 * @experimental
 */
interface StateNamespacesInterface
{
    /**
     * Every namespace: built-in first, then the Solutions'.
     *
     * @return list<string>
     */
    public function all(): array;

    public function has(string $namespace): bool;

    /**
     * The id of the Solution that owns the namespace, or null for a built-in or an unknown one.
     */
    public function ownerOf(string $namespace): ?string;
}
