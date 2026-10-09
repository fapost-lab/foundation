<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\TenantProvisionerInterface;
use Fapost\Foundation\Tenancy\Contracts\TenantReservationInterface;
use Fapost\Foundation\Tenancy\Enums\ProvisioningFailure;
use RuntimeException;
use Throwable;

/**
 * Thrown by {@see TenantProvisionerInterface::provision()} when no tenant was created, and by
 * {@see TenantReservationInterface} when a reservation or a provisioning run did not complete.
 *
 * After `Failed`, `Conflict` or `InProgress` a Pending tenant remains: {@see $tenantId} names it, and
 * {@see TenantReservationInterface::provision()} continues it.
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
        /** The Pending tenant left behind by `Failed`, `InProgress` or `Conflict`; null for every other reason. */
        public readonly ?string $tenantId = null,
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

    public static function failed(string $slug, ?Throwable $previous = null, ?string $tenantId = null): self
    {
        return new self(ProvisioningFailure::Failed, sprintf('Provisioning tenant "%s" failed.', $slug), $previous, $tenantId);
    }

    public static function tenantNotFound(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::TenantNotFound, sprintf('Tenant "%s" was not found.', $tenantId), $previous);
    }

    public static function tenantNotPending(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::TenantNotPending, sprintf('Tenant "%s" is not pending and cannot be provisioned.', $tenantId), $previous);
    }

    public static function conflict(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::Conflict, sprintf('Tenant "%s" cannot be provisioned until an operator resolves a conflict.', $tenantId), $previous, $tenantId);
    }

    public static function inProgress(string $tenantId, ?Throwable $previous = null): self
    {
        return new self(ProvisioningFailure::InProgress, sprintf('Tenant "%s" is already being provisioned.', $tenantId), $previous, $tenantId);
    }
}
