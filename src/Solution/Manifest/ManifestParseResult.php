<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

/**
 * Outcome of {@see ManifestParser::parse()}: a manifest, or every violation found.
 */
final readonly class ManifestParseResult
{
    /**
     * @param  list<ManifestViolation>  $violations  empty exactly when $manifest is set
     */
    public function __construct(
        public string $package,
        public ?SolutionManifest $manifest,
        public array $violations,
    ) {
    }

    public function isValid(): bool
    {
        return null !== $this->manifest;
    }

    /**
     * One line per violation, each starting with the package name.
     *
     * @return list<string>
     */
    public function lines(): array
    {
        return array_map(fn (ManifestViolation $violation): string => $violation->describe($this->package), $this->violations);
    }
}
