<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

/**
 * A tenant that holds its slug but has no schema yet.
 */
final readonly class ReservedTenant
{
    public function __construct(
        /** Tenant ULID; the key a caller links its own data to and later provisions by. */
        public string $id,
        public string $slug,
    ) {
    }
}
