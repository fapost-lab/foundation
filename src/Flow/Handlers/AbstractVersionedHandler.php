<?php

declare(strict_types=1);

namespace Fapost\Foundation\Flow\Handlers;

use Fapost\Foundation\Contracts\NodeHandlerInterface;
use LogicException;

/**
 * Base class for versioned node handlers.
 *
 * Provides default implementations for type resolution (via TYPE constant),
 * supportedVersions, label, category, and configSchema — removing boilerplate
 * from both Core handlers and external Solution/Plugin handlers.
 */
abstract class AbstractVersionedHandler implements NodeHandlerInterface
{
    public function type(): string
    {
        if (!defined(static::class . '::TYPE')) {
            throw new LogicException(static::class . ' must define TYPE constant');
        }

        /** @var string $type */
        $type = constant(static::class . '::TYPE');

        return $type;
    }

    /**
     * @return array<int>
     */
    public function supportedVersions(): array
    {
        return [$this->version()];
    }

    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type()));
    }

    /**
     * @deprecated since foundation 0.37: override it and return a {@see \Fapost\Foundation\Flow\Enums\NodeCategory}
     *             value, or your Solution id. `Core` is not a palette group, so Core 0.36+ refuses a handler that
     *             keeps it at boot. Abstract in 1.0.
     */
    public function category(): string
    {
        return 'Core';
    }

    /**
     * @return array<string, mixed>
     */
    public function configSchema(): array
    {
        return [];
    }
}
