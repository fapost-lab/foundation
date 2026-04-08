<?php

declare(strict_types=1);

namespace FAPost\Foundation\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Marker interface for model utility classes resolved via InteractWithUtilities.
 *
 * Implementations receive the owning model instance as a constructor argument.
 * Register utilities against a model by setting `$utilitiesClass` on the model.
 *
 * Placed in fapost/foundation so Solutions and Plugins can implement
 * domain-specific utilities without depending on Core internals.
 */
interface ModelUtilityInterface
{
    public function __construct(Model $model);
}
