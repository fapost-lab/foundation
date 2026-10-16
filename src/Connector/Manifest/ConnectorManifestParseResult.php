<?php

declare(strict_types=1);

namespace Fapost\Foundation\Connector\Manifest;

use Fapost\Foundation\Solution\Manifest\ManifestViolation;

/**
 * Outcome of {@see ConnectorManifestParser::parse()}: a manifest, or every violation found.
 */
final readonly class ConnectorManifestParseResult
{
    /**
     * @param  list<ManifestViolation>  $violations  empty exactly when $manifest is set
     */
    public function __construct(
        public string $package,
        public ?ConnectorManifest $manifest,
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
