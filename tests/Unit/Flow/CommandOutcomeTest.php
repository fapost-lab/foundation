<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Flow;

use Fapost\Foundation\Flow\Commands\CommandOutcome;
use Fapost\Foundation\Flow\Commands\CommandOutcomeKind;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CommandOutcomeTest extends TestCase
{
    public function test_each_outcome_says_what_core_does(): void
    {
        $reply = CommandOutcome::reply('Thanks!');
        $this->assertSame(CommandOutcomeKind::Reply, $reply->kind);
        $this->assertSame('Thanks!', $reply->reply);
        $this->assertNull($reply->flowId);

        $none = CommandOutcome::none();
        $this->assertSame(CommandOutcomeKind::Reply, $none->kind);
        $this->assertNull($none->reply);

        $end = CommandOutcome::endSession('Bye');
        $this->assertSame(CommandOutcomeKind::EndSession, $end->kind);
        $this->assertSame('Bye', $end->reply);

        $start = CommandOutcome::startFlow('01JFLOW', 'Starting');
        $this->assertSame(CommandOutcomeKind::StartFlow, $start->kind);
        $this->assertSame('01JFLOW', $start->flowId);
        $this->assertSame('Starting', $start->reply);
    }

    public function test_an_empty_reply_sends_nothing(): void
    {
        $this->assertNull(CommandOutcome::reply('')->reply);
        $this->assertNull(CommandOutcome::reply(null)->reply);
        $this->assertNull(CommandOutcome::endSession('')->reply);
        $this->assertNull(CommandOutcome::startFlow('01JFLOW', '')->reply);
    }

    public function test_starting_a_flow_needs_its_id(): void
    {
        foreach (['', ' '] as $flowId) {
            try {
                CommandOutcome::startFlow($flowId);
                $this->fail('A blank flow id was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
