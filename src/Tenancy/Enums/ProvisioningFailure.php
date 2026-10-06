<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Enums;

/**
 * Why a tenant provisioner refused or failed to create a tenant.
 *
 * Lets a caller tell "show this to the user" (every case but Failed) from "platform failure"
 * (Failed) without knowing any Core exception type.
 */
enum ProvisioningFailure: string
{
    case SlugInvalid              = 'slug_invalid';
    case SlugReserved             = 'slug_reserved';
    case SlugTaken                = 'slug_taken';
    case AdminCredentialsMissing  = 'admin_credentials_missing';
    case AdminPasswordHashInvalid = 'admin_password_hash_invalid';
    case Failed                   = 'failed';

    /**
     * Whether the cause is the caller's input rather than a platform failure.
     */
    public function isInputError(): bool
    {
        return self::Failed !== $this;
    }
}
