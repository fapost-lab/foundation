<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Why a tenant decommissioner refused or failed to remove a tenant.
 *
 * Lets a caller tell "the tenant is still being provisioned" (Pending) from "platform failure" (Failed) without
 * knowing any Core exception type. New cases may be added in a minor release: callers matching on it keep a
 * default arm.
 *
 * @experimental
 */
enum DecommissionFailure: string
{
    /** The tenant is still Pending (being provisioned) and is not removed here; nothing was deleted. */
    case Pending = 'pending';

    /** A platform failure (database, provider and the like); the exception's `previous` carries the cause. */
    case Failed = 'failed';
}
