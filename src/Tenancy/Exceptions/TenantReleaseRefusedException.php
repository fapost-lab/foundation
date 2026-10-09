<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\TenantReservationInterface;
use RuntimeException;

/**
 * Thrown by {@see TenantReservationInterface::release()} when the tenant exists but may not be released.
 *
 * Nothing was deleted. A tenant that has claimed a schema is removed only by an operator.
 */
final class TenantReleaseRefusedException extends RuntimeException
{
    private function __construct(
        public readonly string $tenantId,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** The tenant is not Pending (it is active, inactive or suspended). */
    public static function notPending(string $tenantId): self
    {
        return new self($tenantId, sprintf('Tenant "%s" is not pending and cannot be released.', $tenantId));
    }

    /** Provisioning claimed a schema for the tenant, so it holds data an operator must remove. */
    public static function provisioningStarted(string $tenantId): self
    {
        return new self($tenantId, sprintf('Tenant "%s" has started provisioning and cannot be released.', $tenantId));
    }

    /** Another run is provisioning the tenant right now. */
    public static function inProgress(string $tenantId): self
    {
        return new self($tenantId, sprintf('Tenant "%s" is being provisioned and cannot be released.', $tenantId));
    }
}
