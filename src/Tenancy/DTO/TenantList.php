<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

/**
 * One page of a tenant list.
 */
final readonly class TenantList
{
    /**
     * @param  list<TenantSummary>  $items
     * @param  int  $total  matching tenants across all pages
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {}
}
