<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\DTO;

use DateTimeImmutable;
use Fapost\Foundation\DTO\NodeExecutionResult;
use Fapost\Foundation\DTO\NodeExecutionStatus;
use PHPUnit\Framework\TestCase;

/**
 * The named constructors are the contract handlers are written against: each
 * sets its status, and a new optional argument never changes what an existing
 * call means.
 */
final class NodeExecutionResultTest extends TestCase
{
    public function test_delayed_without_a_resume_time_keeps_its_previous_meaning(): void
    {
        $result = NodeExecutionResult::delayed(['flow.step' => 2], ['reason' => 'later']);

        $this->assertSame(NodeExecutionStatus::Delayed, $result->status);
        $this->assertSame(['flow.step' => 2], $result->stateChanges);
        $this->assertSame(['reason' => 'later'], $result->metadata);
        $this->assertNull($result->resumeAt);
    }

    public function test_delayed_carries_the_resume_time(): void
    {
        $resumeAt = new DateTimeImmutable('2030-01-01T10:00:00+00:00');

        $result = NodeExecutionResult::delayed(resumeAt: $resumeAt);

        $this->assertSame(NodeExecutionStatus::Delayed, $result->status);
        $this->assertSame($resumeAt, $result->resumeAt);
        $this->assertSame([], $result->stateChanges);
    }

    public function test_every_named_constructor_sets_its_status_and_no_resume_time(): void
    {
        foreach ([
            [NodeExecutionStatus::Executed, NodeExecutionResult::executed(sourceHandle: 'default')],
            [NodeExecutionStatus::Waiting, NodeExecutionResult::waiting()],
            [NodeExecutionStatus::Finished, NodeExecutionResult::finished()],
            [NodeExecutionStatus::Failed, NodeExecutionResult::failed('boom')],
        ] as [$status, $result]) {
            $this->assertSame($status, $result->status);
            $this->assertNull($result->resumeAt);
        }
    }

    public function test_failed_carries_its_message(): void
    {
        $this->assertSame('boom', NodeExecutionResult::failed('boom')->errorMessage);
    }

    public function test_named_constructors_carry_no_trace(): void
    {
        foreach ([
            NodeExecutionResult::executed(),
            NodeExecutionResult::waiting(),
            NodeExecutionResult::delayed(),
            NodeExecutionResult::finished(),
            NodeExecutionResult::failed('boom'),
        ] as $result) {
            $this->assertSame([], $result->trace);
        }
    }

    public function test_constructor_carries_the_trace(): void
    {
        $trace = ['input' => ['target' => 'https://example.test'], 'output' => ['status' => 200]];

        $result = new NodeExecutionResult(
            status: NodeExecutionStatus::Executed,
            sourceHandle: 'success',
            logResolved: ['status' => 200],
            trace: $trace,
        );

        $this->assertSame($trace, $result->trace);
        $this->assertSame(['status' => 200], $result->logResolved);
    }
}
