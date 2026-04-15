<?php

declare(strict_types=1);

namespace FAPost\Foundation\Manifest;

use FAPost\Foundation\Support\Version;
use InvalidArgumentException;

/**
 * Typed manifest for Solution/Plugin extensions.
 *
 * Used for:
 * - self-hosted boot-time validation (platform:update Phase 1: VALIDATE)
 * - capabilities compatibility check
 * - degraded state detection on boot failure
 *
 * Example in a ServiceProvider:
 *
 *   public function getManifest(): SolutionManifest
 *   {
 *       return SolutionManifest::make(
 *           id: 'hr',
 *           version: '2.1.0',
 *           requiresPlatform: '>=1.4.0 <2.0.0',
 *           requiresCapabilities: [
 *               'flow.node_registry',
 *               'flow.data_accessor',
 *               'contacts.attributes',
 *           ],
 *       );
 *   }
 */
final readonly class SolutionManifest
{
    /**
     * @param  string[]  $requiresCapabilities
     */
    private function __construct(
        /** Unique extension identifier. For example: "hr", "recruitment". */
        public string $id,

        /** Extension semantic version. For example: "2.1.0". */
        public string $version,

        /** Version constraint for fapost/core. For example: ">=1.4.0 <2.0.0". */
        public string $requiresPlatform,

        /**
         * Explicit list of platform capabilities required by the extension.
         * The extension must not start if any required capability is missing.
         *
         * @var string[]
         */
        public array $requiresCapabilities,
    ) {
    }

    /**
     * @param  string[]  $requiresCapabilities
     */
    public static function make(
        string $id,
        string $version,
        string $requiresPlatform,
        array $requiresCapabilities = [],
    ): self {
        if (empty($id)) {
            throw new InvalidArgumentException('SolutionManifest: id cannot be empty');
        }

        if ( ! Version::isValid($version)) {
            throw new InvalidArgumentException("SolutionManifest: invalid version '{$version}'");
        }

        return new self(
            id: $id,
            version: $version,
            requiresPlatform: $requiresPlatform,
            requiresCapabilities: $requiresCapabilities,
        );
    }

    /**
     * Return the platform version constraint declared by the manifest.
     * Semver constraint evaluation is implemented in Core (Composer\Semver).
     */
    public function getPlatformConstraint(): string
    {
        return $this->requiresPlatform;
    }

    /**
     * @return string[]
     */
    public function getRequiredCapabilities(): array
    {
        return $this->requiresCapabilities;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'version'               => $this->version,
            'requires_platform'     => $this->requiresPlatform,
            'requires_capabilities' => $this->requiresCapabilities,
        ];
    }
}
