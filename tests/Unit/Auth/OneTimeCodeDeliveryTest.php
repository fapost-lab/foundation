<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Auth;

use Fapost\Foundation\Auth\DTO\OneTimeCodeDelivery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OneTimeCodeDeliveryTest extends TestCase
{
    public function test_a_sent_delivery_has_no_error(): void
    {
        $delivery = OneTimeCodeDelivery::sent('provider-1');

        $this->assertTrue($delivery->sent);
        $this->assertSame('provider-1', $delivery->providerMessageId);
        $this->assertNull($delivery->error);
    }

    public function test_a_failed_delivery_carries_its_reason(): void
    {
        $delivery = OneTimeCodeDelivery::failed('provider_rejected');

        $this->assertFalse($delivery->sent);
        $this->assertSame('provider_rejected', $delivery->error);
    }

    public function test_a_failed_delivery_needs_a_reason(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OneTimeCodeDelivery::failed(' ');
    }
}
