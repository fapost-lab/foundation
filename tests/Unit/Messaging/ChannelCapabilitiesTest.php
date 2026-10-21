<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Messaging;

use Fapost\Foundation\Messaging\ChannelCapabilities;
use Fapost\Foundation\Messaging\ChannelCapability;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ChannelCapabilitiesTest extends TestCase
{
    public function test_nothing_declared_supports_nothing(): void
    {
        $capabilities = new ChannelCapabilities();

        foreach (ChannelCapability::cases() as $capability) {
            $this->assertFalse($capabilities->supports($capability));
        }
    }

    public function test_it_supports_what_it_declares(): void
    {
        $capabilities = new ChannelCapabilities([ChannelCapability::CallbackButtons, ChannelCapability::RemoveButtons]);

        $this->assertTrue($capabilities->supports(ChannelCapability::CallbackButtons));
        $this->assertTrue($capabilities->supports(ChannelCapability::RemoveButtons));
        $this->assertFalse($capabilities->supports(ChannelCapability::ShareContact));
    }

    public function test_duplicates_are_dropped(): void
    {
        $capabilities = new ChannelCapabilities([ChannelCapability::ReplyButtons, ChannelCapability::ReplyButtons]);

        $this->assertSame([ChannelCapability::ReplyButtons], $capabilities->features);
    }

    public function test_a_non_capability_element_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChannelCapabilities(['callback_buttons']);
    }
}
