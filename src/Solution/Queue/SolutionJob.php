<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Queue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Facade;

/**
 * A queued unit of work of a Solution, run inside one tenant.
 *
 * To write one: extend this class, implement {@see self::solution()} (the id of your Solution) and
 * {@see self::perform()}. Keep only scalars, null, backed enums and arrays of them as properties;
 * pass ids and re-read the data in perform(). Dispatch only through {@see SolutionQueueInterface},
 * never with Laravel's dispatcher. Core switches into the job's tenant before perform() is called.
 * A subclass may override $tries and $timeout; the timeout may be at most
 * {@see SolutionQueueInterface::MAX_TIMEOUT_SECONDS}.
 *
 * @experimental
 */
abstract class SolutionJob implements ShouldQueue
{
    /** Read by Laravel's dispatcher; set by Core when it routes the job. */
    public ?string $connection = null;

    /** Read by Laravel's dispatcher; set by Core when it routes the job to the Solution's queue. */
    public ?string $queue = null;

    /** Read by Laravel's dispatcher; set by Core from the delay given to the dispatch. */
    public mixed $delay = null;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * The tenant the job runs in. Set only by Core's dispatcher.
     *
     * @internal
     */
    public ?string $tenantId = null;

    /**
     * How many times Core has put the job back because its tenant was stopped or its Solution's
     * tables were not ready yet; Core gives up after a limit. Set only by Core.
     *
     * @internal
     */
    public int $postponements = 0;

    /**
     * @return string the id of the Solution that owns the job
     */
    abstract public function solution(): string;

    /**
     * The work. Core has switched into the job's tenant before this is called.
     */
    abstract protected function perform(): void;

    /**
     * @internal Called by Core's dispatcher
     */
    final public function forTenant(string $tenantId): static
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    final public function handle(): void
    {
        $this->perform();
    }

    /**
     * @return list<object>
     *
     * @throws SolutionJobRejectedException when no Core runtime is available
     */
    final public function middleware(): array
    {
        $app = Facade::getFacadeApplication();

        if (null === $app || ! $app->bound(SolutionJobMiddlewareInterface::class)) {
            throw SolutionJobRejectedException::noRuntime();
        }

        return [$app->make(SolutionJobMiddlewareInterface::class)];
    }

    /**
     * Distinguishes jobs of one class for Laravel's uniqueness lock (only when the subclass
     * implements ShouldBeUnique); override it to include the job's arguments.
     */
    public function uniqueKey(): string
    {
        return static::class;
    }

    final public function uniqueId(): string
    {
        return ($this->tenantId ?? '') . ':' . $this->solution() . ':' . $this->uniqueKey();
    }
}
