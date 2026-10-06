<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\TenantList;
use Fapost\Foundation\Tenancy\DTO\TenantListQuery;
use Fapost\Foundation\Tenancy\DTO\TenantSummary;

/**
 * Read-only view of the tenants a platform hosts: what an operator needs to find and open one.
 *
 * Implemented by Core; extension and operator packages only call it. It exposes no schema,
 * configuration or write operation.
 */
interface TenantDirectoryInterface
{
    /**
     * One page of tenants matching the query.
     */
    public function list(TenantListQuery $query): TenantList;

    public function find(string $id): ?TenantSummary;

    /**
     * Unknown ids are absent from the result.
     *
     * @param  list<string>  $ids
     *
     * @return array<string, TenantSummary> keyed by tenant id
     */
    public function findMany(array $ids): array;
}
