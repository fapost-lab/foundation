<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Messaging;

use Fapost\Foundation\Messaging\Button;
use Fapost\Foundation\Messaging\ButtonAction;
use Fapost\Foundation\Messaging\ButtonSet;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ButtonSetTest extends TestCase
{
    public function test_a_callback_set_expects_a_callback(): void
    {
        $set = new ButtonSet([[new Button('A', payload: 'a'), new Button('B', payload: 'b')], [new Button('C', payload: 'c')]]);

        $this->assertTrue($set->expectsCallback());
        $this->assertCount(2, $set->rows);
    }

    public function test_a_reply_set_does_not_expect_a_callback(): void
    {
        $set = new ButtonSet([[new Button('A', ButtonAction::Reply), new Button('B', ButtonAction::ShareContact)]]);

        $this->assertFalse($set->expectsCallback());
    }

    public function test_keyed_rows_and_buttons_are_reindexed_into_lists(): void
    {
        $set = new ButtonSet([
            5      => ['x' => new Button('A', payload: 'a'), 7 => new Button('B', payload: 'b')],
            'next' => [3 => new Button('C', payload: 'c')],
        ]);

        $this->assertSame([0, 1], array_keys($set->rows));
        $this->assertSame([0, 1], array_keys($set->rows[0]));
        $this->assertSame([0], array_keys($set->rows[1]));
        $this->assertTrue($set->expectsCallback());
    }

    public function test_no_rows_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ButtonSet([]);
    }

    public function test_an_empty_row_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ButtonSet([[new Button('A', ButtonAction::Reply)], []]);
    }

    public function test_a_non_button_element_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ButtonSet([['A']]);
    }

    public function test_callback_buttons_cannot_be_mixed_with_other_actions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ButtonSet([[new Button('A', payload: 'a')], [new Button('B', ButtonAction::Reply)]]);
    }
}
