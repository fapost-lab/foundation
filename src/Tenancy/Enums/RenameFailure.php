<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Why a tenant renamer refused or failed to change a tenant's slug.
 *
 * Lets a caller tell "show this to the operator" (input errors) from "platform failure" (Failed)
 * without knowing any Core exception type. New cases may be added in a minor release: callers
 * matching on it keep a default arm.
 */
enum RenameFailure: string
{
    /** The new slug is not a valid DNS label; the exception's `problem` says what is wrong. */
    case SlugInvalid = 'slug_invalid';

    /** The new slug is on the reserved list (including platform and ingress labels). */
    case SlugReserved = 'slug_reserved';

    /**
     * The new slug belongs to another tenant in any status, was another tenant's original slug, or is
     * another tenant's former slug; also a lost race with a concurrent reservation.
     */
    case SlugTaken = 'slug_taken';

    /** No tenant with this id. */
    case TenantNotFound = 'tenant_not_found';

    /** The tenant is still Pending: an unfinished registration holds its slug. */
    case TenantPending = 'tenant_pending';

    /** The installation does not resolve tenants by host, so a slug is not an address and cannot change. */
    case Unavailable = 'unavailable';

    /** A platform failure (database and the like); the exception's `previous` carries the cause. */
    case Failed = 'failed';

    /**
     * Whether the cause is the caller's input or the tenant's state rather than a platform failure.
     * Repeating helps only after `Failed`.
     */
    public function isInputError(): bool
    {
        return self::Failed !== $this;
    }
}
