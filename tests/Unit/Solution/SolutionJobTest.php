<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Closure;
use Fapost\Foundation\Solution\Queue\SolutionJob;
use Fapost\Foundation\Solution\Queue\SolutionJobMiddlewareInterface;
use Fapost\Foundation\Solution\Queue\SolutionJobRejectedException;
use Fapost\Foundation\Solution\Queue\SolutionQueueInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

final class SolutionJobTest extends TestCase
{
    protected function tearDown(): void
    {
        Facade::setFacadeApplication(null);
    }

    public function test_defaults_are_those_laravel_reads(): void
    {
        $job = new RecordingSolutionJob();

        $this->assertInstanceOf(ShouldQueue::class, $job);
        $this->assertNull($job->connection);
        $this->assertNull($job->queue);
        $this->assertNull($job->delay);
        $this->assertSame(3, $job->tries);
        $this->assertSame(300, $job->timeout);
        $this->assertNull($job->tenantId);
        $this->assertSame(0, $job->postponements);
        $this->assertSame(1500, SolutionQueueInterface::MAX_TIMEOUT_SECONDS);
    }

    public function test_handle_calls_perform(): void
    {
        $job = new RecordingSolutionJob();
        $job->handle();

        $this->assertSame(1, $job->performed);
    }

    public function test_for_tenant_sets_the_tenant_and_returns_the_job(): void
    {
        $job = new RecordingSolutionJob();

        $this->assertSame($job, $job->forTenant('t1'));
        $this->assertSame('t1', $job->tenantId);
    }

    public function test_unique_id_joins_tenant_solution_and_key(): void
    {
        $job = new RecordingSolutionJob();
        $this->assertSame(':hr:' . RecordingSolutionJob::class, $job->uniqueId());

        $job->forTenant('t1');
        $this->assertSame('t1:hr:' . RecordingSolutionJob::class, $job->uniqueId());
    }

    public function test_middleware_without_an_application_is_refused(): void
    {
        $this->expectException(SolutionJobRejectedException::class);
        $this->expectExceptionMessage('No Solution job runtime');

        (new RecordingSolutionJob())->middleware();
    }

    public function test_middleware_without_a_binding_is_refused(): void
    {
        Facade::setFacadeApplication(new class () {
            public function bound(string $abstract): bool
            {
                return false;
            }
        });

        $this->expectException(SolutionJobRejectedException::class);

        (new RecordingSolutionJob())->middleware();
    }

    public function test_middleware_returns_the_bound_gate(): void
    {
        $gate = new class () implements SolutionJobMiddlewareInterface {
            public function handle(SolutionJob $job, Closure $next): mixed
            {
                return $next($job);
            }
        };
        Facade::setFacadeApplication(new class ($gate) {
            public function __construct(private readonly object $gate)
            {
            }

            public function bound(string $abstract): bool
            {
                return SolutionJobMiddlewareInterface::class === $abstract;
            }

            public function make(string $abstract): object
            {
                return $this->gate;
            }
        });

        $this->assertSame([$gate], (new RecordingSolutionJob())->middleware());
    }

    public function test_rejection_messages_name_the_job(): void
    {
        $messages = [
            SolutionJobRejectedException::noTenant('J')->getMessage()                                 => ['J', 'outside a tenant'],
            SolutionJobRejectedException::notInstalled('J', 'hr')->getMessage()                       => ['hr', 'not installed'],
            SolutionJobRejectedException::notOwned('J', 'hr')->getMessage()                           => ['hr', 'not a class of the package'],
            SolutionJobRejectedException::unsupportedProperty('J', 'user', 'App\\User')->getMessage() => ['$user', 'App\\User'],
            SolutionJobRejectedException::timeoutTooLong('J', 2000, 1500)->getMessage()               => ['2000', '1500'],
            SolutionJobRejectedException::noRuntime()->getMessage()                                   => ['No Solution job runtime'],
        ];

        foreach ($messages as $message => $needles) {
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $message);
            }
        }
    }
}

final class RecordingSolutionJob extends SolutionJob
{
    public int $performed = 0;

    public function solution(): string
    {
        return 'hr';
    }

    protected function perform(): void
    {
        $this->performed++;
    }
}
