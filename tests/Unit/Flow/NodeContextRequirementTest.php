<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Flow;

use Fapost\Foundation\Contracts\NodeHandlerInterface;
use Fapost\Foundation\DTO\NodeExecutionContext;
use Fapost\Foundation\DTO\NodeExecutionResult;
use Fapost\Foundation\Flow\Contracts\DeclaresContextRequirementInterface;
use Fapost\Foundation\Flow\Enums\NodeContextRequirement;
use Fapost\Foundation\Flow\Handlers\AbstractVersionedHandler;
use PHPUnit\Framework\TestCase;

final class NodeContextRequirementTest extends TestCase
{
    public function test_the_values_are_the_ones_core_stores_and_reports(): void
    {
        $this->assertSame(
            ['contact', 'none', 'any'],
            array_map(static fn (NodeContextRequirement $case): string => $case->value, NodeContextRequirement::cases()),
        );
    }

    public function test_a_handler_declares_its_requirement_by_implementing_the_optional_interface(): void
    {
        $declaring = new class () extends AbstractVersionedHandler implements DeclaresContextRequirementInterface {
            public function type(): string
            {
                return 'demo.tick';
            }

            public function version(): int
            {
                return 1;
            }

            public function category(): string
            {
                return 'demo';
            }

            public function execute(array $nodeConfig, array $state, NodeExecutionContext $context): NodeExecutionResult
            {
                return NodeExecutionResult::executed();
            }

            public function contextRequirement(): NodeContextRequirement
            {
                return NodeContextRequirement::Any;
            }
        };

        $this->assertInstanceOf(NodeHandlerInterface::class, $declaring);
        $this->assertSame(NodeContextRequirement::Any, $declaring->contextRequirement());
    }

    public function test_the_base_handler_declares_nothing_so_it_needs_a_contact(): void
    {
        $this->assertNotContains(
            DeclaresContextRequirementInterface::class,
            class_implements(AbstractVersionedHandler::class),
        );
    }
}
