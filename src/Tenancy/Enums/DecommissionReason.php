<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Why a tenant is being removed completely.
 *
 * Passed to the tenant decommissioner and carried by the event it dispatches, so an operator package can tell
 * the two apart. New cases may be added in a minor release: callers matching on it keep a default arm.
 *
 * @experimental
 */
enum DecommissionReason: string
{
    /** The tenant's own owner asked to delete the workspace. */
    case OwnerRequest = 'owner_request';

    /** The operator's plan retention ran out. */
    case Retention = 'retention';
}
