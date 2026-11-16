<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Queue;

use LogicException;

/**
 * Thrown by {@see SolutionQueueInterface::dispatch()} when Core refuses a Solution job, and by
 * {@see SolutionJob::middleware()} when no Core runtime is available. Nothing was queued.
 *
 * @experimental
 */
final class SolutionJobRejectedException extends LogicException
{
    public static function noTenant(string $jobClass): self
    {
        return new self(sprintf('Solution job %s was dispatched outside a tenant; dispatch it while a tenant is current.', $jobClass));
    }

    public static function notInstalled(string $jobClass, string $solutionId): self
    {
        return new self(sprintf('Solution job %s belongs to Solution "%s", which is not installed.', $jobClass, $solutionId));
    }

    public static function notOwned(string $jobClass, string $solutionId): self
    {
        return new self(sprintf('Solution job %s is not a class of the package of Solution "%s" that it declares.', $jobClass, $solutionId));
    }

    public static function unsupportedProperty(string $jobClass, string $property, string $type): self
    {
        return new self(sprintf(
            'Solution job %s has property $%s of type %s; only scalars, null, backed enums and arrays of them are allowed.',
            $jobClass,
            $property,
            $type,
        ));
    }

    public static function timeoutTooLong(string $jobClass, int $timeout, int $max): self
    {
        return new self(sprintf('Solution job %s has a timeout of %d seconds; the maximum is %d.', $jobClass, $timeout, $max));
    }

    public static function noRuntime(): self
    {
        return new self('No Solution job runtime is available: the application does not bind the Solution job middleware.');
    }
}
