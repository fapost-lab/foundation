<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use RuntimeException;

/**
 * Thrown by {@see \Fapost\Foundation\Tenancy\Contracts\SupportAccessInterface::issue()} when no grant
 * was issued.
 */
final class SupportAccessUnavailableException extends RuntimeException
{
    public static function disabled(): self
    {
        return new self('Support access is not enabled on this platform.');
    }

    public static function tenantNotFound(string $tenantId): self
    {
        return new self(sprintf('Tenant "%s" was not found.', $tenantId));
    }

    public static function tenantNotActive(string $tenantId): self
    {
        return new self(sprintf('Tenant "%s" is not active.', $tenantId));
    }
}
