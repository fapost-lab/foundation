<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\ProvisionedTenant;
use Fapost\Foundation\Tenancy\DTO\ProvisionTenant;
use Fapost\Foundation\Tenancy\Exceptions\TenantProvisioningFailedException;

/**
 * Creates a tenant: its registry row, database schema, migrations, first administrator.
 *
 * Implemented by Core; extension and operator packages only call it.
 *
 * Synchronous and slow (the full tenant schema is migrated): callers must run it outside
 * HTTP requests, typically from a console command or a queued job.
 *
 * After a failure with reason `Failed` the slug is held by a Pending tenant, whose id is in
 * `TenantProvisioningFailedException::$tenantId`; a retry of this method gets `SlugTaken`. To continue,
 * call {@see TenantReservationInterface::provision()} with that id and the administrator.
 */
interface TenantProvisionerInterface
{
    /**
     * @throws TenantProvisioningFailedException
     */
    public function provision(ProvisionTenant $request): ProvisionedTenant;
}
