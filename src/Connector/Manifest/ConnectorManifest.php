<?php

declare(strict_types=1);

namespace Fapost\Foundation\Connector\Manifest;

use Fapost\Foundation\Solution\Manifest\InvalidManifestException;

/**
 * The declared identity of a connector, read from its package's `composer.json` without running
 * any of its code. See {@see ConnectorManifestSchema} for the format and {@see ConnectorManifestParser}
 * for the rules.
 *
 * The package version is not part of it: Composer owns that, and Core reads it from `installed.json`.
 * Since foundation 0.21, experimental.
 */
final readonly class ConnectorManifest
{
    /**
     * @param  string  $package  composer package name, such as "acme/fapost-telegram"
     * @param  string  $id  stable key of the connector; it never changes
     * @param  string  $name  what an operator reads, in English
     * @param  string  $provider  class name of the connector's service provider
     * @param  list<string>  $channels  channel types the connector owns
     * @param  string  $foundationConstraint  the package's `require` for fapost/foundation, as written
     */
    public function __construct(
        public string $package,
        public int $schema,
        public string $id,
        public string $name,
        public ?string $description,
        public string $provider,
        public array $channels,
        public string $foundationConstraint,
    ) {
    }

    /**
     * @param  array<string, mixed>  $composer  decoded composer.json of the package
     *
     * @throws InvalidManifestException with every violation found
     */
    public static function fromComposer(array $composer): self
    {
        $result = (new ConnectorManifestParser())->parse($composer);

        return $result->manifest ?? throw new InvalidManifestException($result->package, $result->violations);
    }
}
