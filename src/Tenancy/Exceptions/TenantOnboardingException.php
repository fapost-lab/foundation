<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\TenantOnboardingInterface;
use RuntimeException;
use Throwable;

/**
 * Thrown by {@see TenantOnboardingInterface} when the tenant cannot be reached.
 *
 * The top-level message never contains internal details. Messages in the `previous` chain come from
 * Core and are not redacted.
 */
final class TenantOnboardingException extends RuntimeException
{
    private function __construct(
        public readonly string $tenantId,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function tenantNotFound(string $tenantId, ?Throwable $previous = null): self
    {
        return new self($tenantId, sprintf('Tenant "%s" was not found.', $tenantId), $previous);
    }

    public static function tenantNotActive(string $tenantId, ?Throwable $previous = null): self
    {
        return new self($tenantId, sprintf('Tenant "%s" is not active.', $tenantId), $previous);
    }
}
