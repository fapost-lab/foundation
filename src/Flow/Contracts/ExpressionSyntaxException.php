<?php

declare(strict_types=1);

namespace FAPost\Foundation\Flow\Contracts;

use RuntimeException;

/**
 * Thrown by {@see ExpressionEngineInterface::validate()} when the source
 * fails parsability or static reference checks at flow_definition save time.
 *
 * Caller (flow validator) collects these into the validation result and
 * returns 422 to the client on publish, or warning on draft save.
 */
final class ExpressionSyntaxException extends RuntimeException
{
}
