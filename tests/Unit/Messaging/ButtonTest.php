<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Messaging;

use Fapost\Foundation\Messaging\Button;
use Fapost\Foundation\Messaging\ButtonAction;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ButtonTest extends TestCase
{
    /**
     * @return array<string, array{ButtonAction}>
     */
    public static function nonCallbackActions(): array
    {
        return [
            'reply'          => [ButtonAction::Reply],
            'share contact'  => [ButtonAction::ShareContact],
            'share location' => [ButtonAction::ShareLocation],
        ];
    }
    public function test_a_callback_button_keeps_its_label_and_payload(): void
    {
        $button = new Button('Yes', payload: 'abc');

        $this->assertSame('Yes', $button->label);
        $this->assertSame(ButtonAction::Callback, $button->action);
        $this->assertSame('abc', $button->payload);
    }

    public function test_a_payload_of_exactly_the_limit_is_accepted(): void
    {
        $button = new Button('Yes', payload: str_repeat('a', Button::MAX_PAYLOAD_BYTES));

        $this->assertSame(Button::MAX_PAYLOAD_BYTES, mb_strlen((string) $button->payload, '8bit'));
    }

    public function test_the_limit_counts_bytes_not_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Button('Yes', payload: str_repeat('ж', 33));
    }

    public function test_a_payload_over_the_limit_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Button('Yes', payload: str_repeat('a', Button::MAX_PAYLOAD_BYTES + 1));
    }

    public function test_an_empty_label_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Button('', payload: 'abc');
    }

    public function test_a_callback_button_without_a_payload_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Button('Yes');
    }

    public function test_a_callback_button_with_an_empty_payload_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Button('Yes', ButtonAction::Callback, '');
    }

    #[DataProvider('nonCallbackActions')]
    public function test_other_actions_need_no_payload(ButtonAction $action): void
    {
        $button = new Button('Go', $action);

        $this->assertNull($button->payload);
    }

    #[DataProvider('nonCallbackActions')]
    public function test_other_actions_reject_a_payload(ButtonAction $action): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Button('Go', $action, 'abc');
    }
}
