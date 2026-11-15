<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Flow;

use Fapost\Foundation\DTO\NodeExecutionContext;
use Fapost\Foundation\DTO\NodeExecutionResult;
use Fapost\Foundation\Flow\Enums\NodeCategory;
use Fapost\Foundation\Flow\Handlers\AbstractVersionedHandler;
use LogicException;
use PHPUnit\Framework\TestCase;

final class AbstractVersionedHandlerTest extends TestCase
{
    public function test_the_default_category_is_core_which_is_not_a_palette_group(): void
    {
        $handler = $this->handler();

        $this->assertSame('Core', $handler->category());
        $this->assertNull(NodeCategory::tryFrom($handler->category()));
    }

    public function test_a_handler_may_override_the_category(): void
    {
        $handler = new class () extends AbstractVersionedHandler {
            public const TYPE = 'demo';

            public function version(): int
            {
                return 1;
            }

            public function category(): string
            {
                return NodeCategory::cases()[0]->value;
            }

            public function execute(array $nodeConfig, array $state, NodeExecutionContext $context): NodeExecutionResult
            {
                throw new LogicException('not executed');
            }
        };

        $this->assertNotNull(NodeCategory::tryFrom($handler->category()));
    }

    private function handler(): AbstractVersionedHandler
    {
        return new class () extends AbstractVersionedHandler {
            public const TYPE = 'demo';

            public function version(): int
            {
                return 1;
            }

            public function execute(array $nodeConfig, array $state, NodeExecutionContext $context): NodeExecutionResult
            {
                throw new LogicException('not executed');
            }
        };
    }
}
