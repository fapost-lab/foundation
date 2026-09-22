<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\DTO;

use Fapost\Foundation\DTO\NodeExecutionContext;
use PHPUnit\Framework\TestCase;

final class NodeExecutionContextTest extends TestCase
{
    public function test_a_node_is_not_resumed_after_a_delay_unless_the_engine_says_so(): void
    {
        $context = new NodeExecutionContext(
            tenantId: 'tenant',
            contactId: 'contact',
            sessionId: 'session',
            nodeId: 'node',
            idempotencyKey: 'key',
            platform: 'telegram',
        );

        $this->assertFalse($context->resumedAfterDelay);
    }

    public function test_the_engine_can_mark_a_resumed_run(): void
    {
        $context = new NodeExecutionContext(
            tenantId: 'tenant',
            contactId: 'contact',
            sessionId: 'session',
            nodeId: 'node',
            idempotencyKey: 'key',
            platform: 'telegram',
            resumedAfterDelay: true,
        );

        $this->assertTrue($context->resumedAfterDelay);
    }
}
