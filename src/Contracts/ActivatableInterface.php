<?php

declare(strict_types=1);

namespace FAPost\Foundation\Contracts;

/**
 * Contract for any platform extension (Solution, Plugin)
 * that can be activated/deactivated.
 *
 * Boot-time validation: extension must not start when capabilities are incompatible.
 */
interface ActivatableInterface
{
    /**
     * Unique extension identifier.
     * Used in platform:update, manifest validation, and registry.
     */
    public function getId(): string;

    /**
     * Extension semantic version (for example "1.2.3").
     */
    public function getVersion(): string;

    /**
     * Returns SolutionManifest with full dependency description.
     */
    public function getManifest(): \FAPost\Foundation\Manifest\SolutionManifest;

    /**
     * Called after successful activation and boot validation.
     */
    public function onActivate(): void;

    /**
     * Called on deactivation or degraded state.
     * Active sessions continue on their own snapshot; this method
     * only stops registration of new sessions.
     */
    public function onDeactivate(): void;
}
