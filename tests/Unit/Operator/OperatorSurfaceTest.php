<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Operator;

use Fapost\Foundation\Operator\Enums\OperatorSurface;
use PHPUnit\Framework\TestCase;

final class OperatorSurfaceTest extends TestCase
{
    public function test_surfaces_keep_their_published_values(): void
    {
        $this->assertSame(
            ['horizon', 'pulse', 'telescope', 'logs', 'health'],
            array_map(static fn (OperatorSurface $s): string => $s->value, OperatorSurface::cases()),
        );
    }

    public function test_an_unknown_value_is_not_a_surface(): void
    {
        $this->assertNull(OperatorSurface::tryFrom('billing'));
    }
}
