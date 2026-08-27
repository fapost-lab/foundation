<?php

declare(strict_types=1);

namespace Fapost\Foundation\Contracts;

/**
 * Contract for reading canonical module data from the flow engine.
 *
 * A condition node must NEVER read module tables directly.
 * It must always use a DataAccessor. This prevents data duplication
 * and keeps the module as the single source of truth.
 *
 * For example, an HR module registers HrDataAccessor under namespace "hr".
 * The condition node reads module.hr.department and the engine resolves it
 * through the registry to data from employees.
 *
 * DataAccessor implementation belongs to a module, not Core.
 */
interface DataAccessorInterface
{
    /**
     * Namespace of this accessor. For example: "hr", "recruitment".
     * Corresponds to module.{namespace}.* in namespaced state.
     */
    public function namespace(): string;

    /**
     * Get a value by key for a specific contact.
     *
     * @return mixed scalar or null; accessor must not return objects
     */
    public function get(string $key, string $contactId, string $tenantId): mixed;

    /**
     * List of supported keys.
     * Used to validate flow definition during save.
     *
     * @return string[]
     */
    public function supportedKeys(): array;
}
