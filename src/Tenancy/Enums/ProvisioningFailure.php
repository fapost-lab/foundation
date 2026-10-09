<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Why a tenant provisioner refused or failed to create a tenant.
 *
 * Lets a caller tell "show this to the user" (input errors) from "platform failure" (Failed) and
 * "try again later" (InProgress) without knowing any Core exception type. New cases may be added in
 * a minor release: callers matching on it keep a default arm.
 */
enum ProvisioningFailure: string
{
    case SlugInvalid              = 'slug_invalid';
    case SlugReserved             = 'slug_reserved';
    case SlugTaken                = 'slug_taken';
    case AdminCredentialsMissing  = 'admin_credentials_missing';
    case AdminPasswordHashInvalid = 'admin_password_hash_invalid';
    case Failed                   = 'failed';

    /** No tenant with this id (TenantReservationInterface::provision). */
    case TenantNotFound = 'tenant_not_found';

    /** The tenant is neither Pending nor Active (inactive, suspended): provisioning cannot complete it. */
    case TenantNotPending = 'tenant_not_pending';

    /**
     * Provisioning cannot complete without an operator: a schema of the tenant's name that the tenant
     * never claimed, or a user already in the tenant other than the first administrator. Repeating
     * does not help; the tenant stays Pending until the conflict is resolved.
     */
    case Conflict = 'conflict';

    /** Another run holds the tenant's provisioning lease; repeat later. */
    case InProgress = 'in_progress';

    /**
     * Whether the cause is the caller's input rather than a platform failure, a busy tenant or a conflict an operator must resolve.
     */
    public function isInputError(): bool
    {
        return ! in_array($this, [self::Failed, self::InProgress, self::Conflict], true);
    }

    /**
     * Whether repeating {@see \Fapost\Foundation\Tenancy\Contracts\TenantReservationInterface::provision()}
     * may succeed: after `Failed` (resumes where it stopped) and `InProgress` (once the other run ends).
     * Never after `Conflict`, which needs an operator.
     */
    public function isRetryable(): bool
    {
        return self::Failed === $this || self::InProgress === $this;
    }
}
