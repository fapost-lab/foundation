<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Contracts;

use Fapost\Foundation\Solution\DTO\InstalledSolutionInfo;

/**
 * Read-only list of the Solutions installed in this image whose manifest is valid.
 *
 * Implemented by Core from the manifests; an operator package reads it to offer only Solutions
 * that exist (for example as the choices of a plan). It says what is installed, not what a tenant
 * may use or has activated. Not tenant-specific: it depends on the image alone.
 */
interface InstalledSolutionCatalogInterface
{
    /**
     * @return list<InstalledSolutionInfo> ordered by id
     */
    public function all(): array;

    public function find(string $id): ?InstalledSolutionInfo;

    public function has(string $id): bool;
}
