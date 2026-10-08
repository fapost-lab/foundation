<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Exceptions;

use Fapost\Foundation\Tenancy\Contracts\SupportAccessInterface;
use Fapost\Foundation\Tenancy\Enums\SupportAccessFailure;
use RuntimeException;

/**
 * Thrown by {@see SupportAccessInterface::issue()} when no grant
 * was issued; {@see $reason} tells the caller why without parsing the message.
 */
final class SupportAccessUnavailableException extends RuntimeException
{
    private function __construct(
        public readonly SupportAccessFailure $reason,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function disabled(): self
    {
        return new self(SupportAccessFailure::Disabled, 'Support access is not enabled on this platform.');
    }

    public static function tenantNotFound(string $tenantId): self
    {
        return new self(SupportAccessFailure::TenantNotFound, sprintf('Tenant "%s" was not found.', $tenantId));
    }

    public static function tenantNotActive(string $tenantId): self
    {
        return new self(SupportAccessFailure::TenantNotActive, sprintf('Tenant "%s" is not active.', $tenantId));
    }
}
