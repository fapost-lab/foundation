<?php

declare(strict_types=1);

namespace Fapost\Foundation\Contracts;

/**
 * Contract for any platform extension (Solution, Plugin)
 * that can be activated/deactivated.
 *
 * Identity (id, name, version constraint, declared actions) is not part of this contract: it is
 * data in the package's composer.json, see {@see \Fapost\Foundation\Solution\Manifest\ManifestSchema}.
 */
interface ActivatableInterface
{
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
