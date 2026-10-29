<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use Fapost\Foundation\Tenancy\Contracts\TenantDecommissionerInterface;
use InvalidArgumentException;

/**
 * The outcome of {@see TenantDecommissionerInterface::decommission()}.
 *
 * @experimental
 */
final readonly class DecommissionedTenant
{
    /**
     * @param  string  $tenantId  tenant ULID in its lowercase RFC 4122 form
     * @param  string|null  $slug  the slug the tenant had, free again now; null only when {@see self::$alreadyGone}
     * @param  bool  $alreadyGone  true when no such tenant existed, so nothing was removed and no event was dispatched
     * @param  bool  $mediaKeptInClientStorage  true when the tenant's media lived in the client's own storage, which the installation never deletes
     *
     * @throws InvalidArgumentException when the tenant id is empty, or the slug is missing although the tenant existed
     */
    public function __construct(
        public string $tenantId,
        public ?string $slug,
        public bool $alreadyGone,
        public bool $mediaKeptInClientStorage,
    ) {
        if ('' === mb_trim($tenantId)) {
            throw new InvalidArgumentException('A decommissioned tenant needs a tenant id.');
        }

        if (! $alreadyGone && (null === $slug || '' === mb_trim($slug))) {
            throw new InvalidArgumentException('A decommissioned tenant that existed needs its slug.');
        }
    }
}
