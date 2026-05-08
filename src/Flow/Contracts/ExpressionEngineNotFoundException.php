<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Contracts;

use RuntimeException;

/**
 * Thrown when {@see \App\Domains\Flow\Expression\ExpressionEngineRegistry::get()}
 * cannot resolve the requested engine id.
 *
 * Indicates either an operator misconfiguration (tenant.settings.expression_engine
 * points to an engine that is not registered in this build) or stale code (a
 * flow_definition stored an engine id that has since been removed without proper
 * migration).
 */
class ExpressionEngineNotFoundException extends RuntimeException
{
    public static function forId(string $id): self
    {
        return new self(sprintf("Expression engine '%s' is not registered.", $id));
    }
}
