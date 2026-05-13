<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Contracts;

use RuntimeException;

/**
 * Thrown by {@see ExpressionEngineInterface::evaluate()} when an expression
 * cannot be evaluated at runtime (resolution error, type mismatch in a
 * compound expression, engine internal failure).
 *
 * Callers (the flow engine) catch this and fail the session — handlers
 * are not expected to recover from expression failures.
 */
final class ExpressionEvaluationException extends RuntimeException
{
}
