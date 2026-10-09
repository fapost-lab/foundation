<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\ProvisionedTenant;
use Fapost\Foundation\Tenancy\DTO\ReservedTenant;
use Fapost\Foundation\Tenancy\DTO\SlugCheck;
use Fapost\Foundation\Tenancy\DTO\TenantAdmin;
use Fapost\Foundation\Tenancy\Enums\TenantStatus;
use Fapost\Foundation\Tenancy\Exceptions\TenantProvisioningFailedException;
use Fapost\Foundation\Tenancy\Exceptions\TenantReleaseRefusedException;
use InvalidArgumentException;

/**
 * Holds a slug for a tenant that does not exist yet, and completes that tenant later.
 *
 * Implemented by Core; extension and operator packages only call it.
 *
 * A reserved tenant is a {@see TenantStatus::Pending} row: it owns the slug, has no schema, serves
 * no traffic and appears in the tenant directory as `Pending`. {@see self::provision()} turns it into an
 * active tenant, by id, and can be repeated after a failure or a killed worker until it succeeds.
 */
interface TenantReservationInterface
{
    /**
     * No side effects; one indexed query. Advisory: {@see self::reserve()} is what decides.
     */
    public function check(string $slug): SlugCheck;

    /**
     * Creates a Pending tenant (no schema) holding the slug, or returns the tenant already reserved
     * under this key, in any status.
     *
     * Keep the key in your own record before calling: it is the only link between a crashed process
     * and the row it created. A key is bound to the slug it first reserved.
     *
     * @throws TenantProvisioningFailedException with reason SlugInvalid, SlugReserved or SlugTaken
     * @throws InvalidArgumentException the key is empty or longer than 64 characters, or is already
     *                                  bound to another slug
     */
    public function reserve(string $slug, string $reservationKey): ReservedTenant;

    /**
     * Deletes a Pending tenant whose provisioning never claimed a schema. An unknown id is a no-op.
     *
     * @throws TenantReleaseRefusedException the tenant is not Pending, has claimed a schema, or is
     *                                       being provisioned right now
     */
    public function release(string $tenantId): void;

    /**
     * Completes a Pending tenant: schema, migrations, first administrator, activation. Resumes a
     * failed or killed run from where it stopped; on an Active tenant returns at once.
     *
     * Synchronous and slow (the full tenant schema is migrated): run it outside HTTP requests,
     * from a queued job. A run that fails leaves the tenant Pending; repeating the call is safe.
     * Reasons `Failed` and `InProgress` are worth repeating (later, with backoff).
     *
     * @throws TenantProvisioningFailedException
     */
    public function provision(string $tenantId, TenantAdmin $admin): ProvisionedTenant;
}
