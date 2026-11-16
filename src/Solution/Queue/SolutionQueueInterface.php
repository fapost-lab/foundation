<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Queue;

/**
 * The only way a Solution dispatches a {@see SolutionJob}. Implemented by Core, which stamps the
 * current tenant on the job and routes it to the Solution's own queue.
 *
 * Core refuses with {@see SolutionJobRejectedException} a job dispatched outside a tenant, of a
 * Solution that is not installed, whose class is not in that Solution's package, with a property
 * that is not a scalar, null, backed enum or array of them, or whose timeout exceeds
 * {@see self::MAX_TIMEOUT_SECONDS}.
 *
 * @experimental
 */
interface SolutionQueueInterface
{
    public const int MAX_TIMEOUT_SECONDS = 1500;

    /**
     * @param  int  $delaySeconds  0 runs the job as soon as a worker is free
     *
     * @throws SolutionJobRejectedException when the job is refused; nothing was queued
     */
    public function dispatch(SolutionJob $job, int $delaySeconds = 0): void;
}
