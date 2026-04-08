<?php

declare(strict_types=1);

namespace FAPost\Foundation\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only contract for resolving computed model attributes.
 *
 * Placed in fapost/foundation so the HasComputedAttributes trait in
 * fapost/support can depend on this contract without knowing Core internals.
 *
 * Core binds this interface to ModelAttributeRegistry in AppServiceProvider:
 *
 *   $this->app->singleton(ModelAttributeResolverInterface::class, ModelAttributeRegistry::class);
 *
 * @see \App\Domains\Shared\Infrastructure\ModelAttributeRegistry
 */
interface ModelAttributeResolverInterface
{
    /**
     * Check whether a computed attribute resolver is registered for the given model class and attribute name.
     */
    public function has(string $modelClass, string $name): bool;

    /**
     * Resolve a computed attribute value for the provided model instance.
     *
     * @return mixed
     *
     * @throws \LogicException If the attribute is not registered.
     */
    public function resolve(string $modelClass, string $name, Model $model): mixed;

    /**
     * Return resolvers for computed attributes that should be included in array/json output (append=true).
     *
     * @return array<string, \Closure(Model): mixed>
     */
    public function serializable(string $modelClass): array;
}
