<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\TenantAccessState;

/**
 * Answers what a tenant may do right now. Implemented by an operator package; Core's default
 * answers "active" for every tenant, so an installation without one behaves as if every tenant
 * were active.
 *
 * Core asks on every request to a tenant host and before every unit of runtime work (an inbound
 * message, a delayed wake-up, a broadcast send), so an implementation should answer quickly and
 * cache what it reads. The mode is computed, not switched: nothing schedules a change of it.
 */
interface TenantAccessModeInterface
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     */
    public function stateFor(string $tenantId): TenantAccessState;
}
