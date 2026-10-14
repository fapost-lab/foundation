<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Exceptions;

use LogicException;

/**
 * Thrown by Core's console registry: a foreign namespace, a duplicate key, a second application,
 * a closed registry, or a declaration made while no Solution provider is booting.
 *
 * @experimental
 */
final class InvalidConsoleDeclarationException extends LogicException
{
    /**
     * @param  string|null  $solutionId  the Solution whose provider was booting, or null when none was
     */
    public function __construct(
        public readonly ?string $solutionId,
        string $message,
    ) {
        parent::__construct($message);
    }
}
