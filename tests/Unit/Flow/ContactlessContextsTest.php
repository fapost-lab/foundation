<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Flow;

use Fapost\Foundation\Flow\Call\CallContext;
use Fapost\Foundation\Flow\Contracts\ScopedStateReaderInterface;
use Fapost\Foundation\Flow\DTO\ExpressionContext;
use PHPUnit\Framework\TestCase;

final class ContactlessContextsTest extends TestCase
{
    public function test_a_call_without_a_contact_carries_no_contact_id(): void
    {
        $context = new CallContext(
            tenantId: 'tenant',
            contactId: null,
            sessionId: 'session',
            nodeId: 'node',
            idempotencyKey: 'key:node',
        );

        $this->assertNull($context->contactId);
    }

    public function test_an_expression_without_a_contact_carries_no_contact_id(): void
    {
        $reader = new class () implements ScopedStateReaderInterface {
            public function read(string $path): mixed
            {
                return 'flow.answer' === $path ? 42 : null;
            }
        };

        $context = new ExpressionContext(tenantId: 'tenant', contactId: null, sessionId: 'session', stateReader: $reader);

        $this->assertNull($context->contactId);
        $this->assertSame(42, $context->read('flow.answer'));
    }
}
