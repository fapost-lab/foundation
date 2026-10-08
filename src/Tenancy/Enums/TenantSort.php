<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * The field a tenant list is ordered by.
 */
enum TenantSort: string
{
    case Slug      = 'slug';
    case CreatedAt = 'created_at';
}
