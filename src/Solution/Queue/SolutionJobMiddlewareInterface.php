<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Queue;

use Closure;

/**
 * Core's runtime gate around every {@see SolutionJob}: checks that the tenant exists and its access
 * mode allows work, switches into the tenant, and checks that the Solution is active and its schema
 * is ready, before calling the job. Implemented by Core; Solutions never implement or call it.
 *
 * @experimental
 */
interface SolutionJobMiddlewareInterface
{
    public function handle(SolutionJob $job, Closure $next): mixed;
}
