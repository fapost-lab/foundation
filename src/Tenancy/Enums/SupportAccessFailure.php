<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Why no support access grant was issued.
 */
enum SupportAccessFailure: string
{
    case Disabled        = 'disabled';
    case TenantNotFound  = 'tenant_not_found';
    case TenantNotActive = 'tenant_not_active';
}
