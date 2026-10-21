<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Messaging;

use Fapost\Foundation\Messaging\Button;
use Fapost\Foundation\Messaging\ButtonAction;
use Fapost\Foundation\Messaging\ButtonSet;
use Fapost\Foundation\Messaging\MessagePayload;
use PHPUnit\Framework\TestCase;

/**
 * Serializing a whole MessagePayload (a spatie/laravel-data object) needs the Laravel container, so that round trip
 * is covered by Core's tests; the readonly value objects it now carries are checked here without it.
 */
final class MessagePayloadTest extends TestCase
{
    public function test_buttons_default_to_null_and_are_kept_when_given(): void
    {
        $buttons = new ButtonSet([[new Button('Yes', payload: 'p-1')]]);

        $this->assertNull((new MessagePayload(type: 'text', text: 'Hi'))->buttons);
        $this->assertSame($buttons, (new MessagePayload(type: 'keyboard', text: 'Choose', buttons: $buttons))->buttons);
    }

    public function test_a_button_set_survives_php_serialization(): void
    {
        $buttons = new ButtonSet([[new Button('Yes', payload: 'p-1'), new Button('No', payload: 'p-2')]]);

        /** @var ButtonSet $restored */
        $restored = unserialize(serialize($buttons));

        $this->assertEquals($buttons, $restored);
        $this->assertSame(ButtonAction::Callback, $restored->rows[0][1]->action);
        $this->assertSame('p-2', $restored->rows[0][1]->payload);
    }
}
