<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * A tenant's status as the platform records it.
 *
 * New statuses may be added in a minor release (a stopped tenant is planned): callers matching on
 * it keep a default arm.
 */
enum TenantStatus: string
{
    case Active    = 'active';
    case Inactive  = 'inactive';
    case Suspended = 'suspended';
}
