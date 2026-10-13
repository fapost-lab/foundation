<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

use InvalidArgumentException;

final class InvalidManifestException extends InvalidArgumentException
{
    /**
     * @param  list<ManifestViolation>  $violations
     */
    public function __construct(
        public readonly string $package,
        public readonly array $violations,
    ) {
        parent::__construct(implode("\n", array_map(
            static fn (ManifestViolation $violation): string => $violation->describe($package),
            $violations,
        )));
    }
}
