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
 * After a failure with reason `Failed` the slug stays taken (an inactive tenant row) until it is
 * cleaned up, so a retry gets `SlugTaken`. Callers that queue provisioning should not retry it
 * automatically.
 */
interface TenantProvisionerInterface
{
    /**
     * @throws TenantProvisioningFailedException
     */
    public function provision(ProvisionTenant $request): ProvisionedTenant;
}
