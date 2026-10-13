<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

/**
 * An installed Solution as the catalog shows it to an operator package (plan forms, add-on offers).
 */
final readonly class InstalledSolutionInfo
{
    /**
     * @param  string  $id  stable key of the Solution; never changes
     * @param  string  $name  what an operator reads, in English
     * @param  string|null  $version  installed package version as Composer reports it, such as "1.2.0" or "dev-main"
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description = null,
        public ?string $version = null,
    ) {
    }
}
