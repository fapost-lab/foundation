<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

/**
 * The declared identity of a Solution, read from its package's `composer.json` without running
 * any of its code. See {@see ManifestSchema} for the format and {@see ManifestParser} for the rules.
 *
 * The package version is not part of it: Composer owns that, and Core reads it from `installed.json`.
 */
final readonly class SolutionManifest
{
    /**
     * @param  string  $package  composer package name, such as "acme/fapost-feedback"
     * @param  string  $id  stable key of the Solution; plans, activations and action ids hang on it, so it never changes
     * @param  string  $name  what an operator reads, in English
     * @param  string  $provider  class name of the Solution's service provider
     * @param  list<string>  $actions  action ids the Solution registers, each prefixed "<id>."
     * @param  string  $foundationConstraint  the package's `require` for fapost/foundation, as written
     */
    public function __construct(
        public string $package,
        public int $schema,
        public string $id,
        public string $name,
        public ?string $description,
        public string $provider,
        public array $actions,
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
        $result = (new ManifestParser())->parse($composer);

        return $result->manifest ?? throw new InvalidManifestException($result->package, $result->violations);
    }
}
