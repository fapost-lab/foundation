<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Messaging;

use Fapost\Foundation\Messaging\ButtonLimits;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ButtonLimitsTest extends TestCase
{
    /**
     * @return iterable<string, array{int|null, int|null, int|null}>
     */
    public static function nonPositiveLimits(): iterable
    {
        yield 'zero buttons' => [0, null, null];
        yield 'negative per row' => [null, -1, null];
        yield 'zero label length' => [null, null, 0];
    }
    public function test_nothing_declared_is_unlimited(): void
    {
        $limits = new ButtonLimits();

        $this->assertTrue($limits->isUnlimited());
        $this->assertNull($limits->maxButtons);
        $this->assertNull($limits->maxButtonsPerRow);
        $this->assertNull($limits->maxLabelLength);
    }

    public function test_it_keeps_what_it_declares(): void
    {
        $limits = new ButtonLimits(maxButtons: 100, maxButtonsPerRow: 8, maxLabelLength: 75);

        $this->assertFalse($limits->isUnlimited());
        $this->assertSame(100, $limits->maxButtons);
        $this->assertSame(8, $limits->maxButtonsPerRow);
        $this->assertSame(75, $limits->maxLabelLength);
    }

    public function test_one_declared_limit_is_not_unlimited(): void
    {
        $this->assertFalse((new ButtonLimits(maxLabelLength: 75))->isUnlimited());
    }

    #[DataProvider('nonPositiveLimits')]
    public function test_a_non_positive_limit_is_rejected(?int $maxButtons, ?int $maxButtonsPerRow, ?int $maxLabelLength): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ButtonLimits($maxButtons, $maxButtonsPerRow, $maxLabelLength);
    }
}
