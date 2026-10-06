<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\TenantProvisionerInterface;
use Fapost\Foundation\Tenancy\Enums\ProvisioningFailure;
use RuntimeException;
use Throwable;

/**
 * Thrown by {@see TenantProvisionerInterface::provision()} when no tenant was created.
 *
 * The top-level message never contains credentials. Messages in the `previous` chain come from
 * Core and are not redacted.
 */
final class TenantProvisioningFailedException extends RuntimeException
{
    private function __construct(
        public readonly ProvisioningFailure $reason,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function slugInvalid(string $slug, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::SlugInvalid, sprintf('Tenant slug "%s" is not valid.', $slug), $previous);
    }

    public static function slugReserved(string $slug, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::SlugReserved, sprintf('Tenant slug "%s" is reserved.', $slug), $previous);
    }

    public static function slugTaken(string $slug, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::SlugTaken, sprintf('Tenant slug "%s" is already taken.', $slug), $previous);
    }

    public static function adminCredentialsMissing(?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::AdminCredentialsMissing, 'The first admin e-mail and password hash are required.', $previous);
    }

    public static function adminPasswordHashInvalid(?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::AdminPasswordHashInvalid, 'The first admin password must be a hash made by the application hasher.', $previous);
    }

    public static function failed(string $slug, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::Failed, sprintf('Provisioning tenant "%s" failed.', $slug), $previous);
    }
}
