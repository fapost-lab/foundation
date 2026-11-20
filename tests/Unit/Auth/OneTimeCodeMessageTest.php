<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Auth;

use Fapost\Foundation\Auth\DTO\OneTimeCodeMessage;
use Fapost\Foundation\Auth\Enums\OneTimeCodeChannel;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use SensitiveParameter;

final class OneTimeCodeMessageTest extends TestCase
{
    private const string CODE = '482913';

    public function test_it_exposes_the_code_only_through_its_accessor(): void
    {
        $message = $this->message();

        $this->assertSame(self::CODE, $message->code());
        $this->assertSame('client@example.com', $message->destination);
        $this->assertSame(OneTimeCodeChannel::Email, $message->channel);
        $this->assertStringNotContainsString(self::CODE, (string) json_encode($message));
    }

    public function test_var_dump_and_print_r_mask_the_code(): void
    {
        $message = $this->message();

        ob_start();
        var_dump($message);
        $dumped = (string) ob_get_clean();

        $this->assertStringNotContainsString(self::CODE, $dumped);
        $this->assertStringNotContainsString(self::CODE, print_r($message, true));
        $this->assertStringContainsString('***', $dumped);
    }

    public function test_it_cannot_be_serialized(): void
    {
        $this->expectException(LogicException::class);

        serialize($this->message());
    }

    public function test_the_code_is_a_sensitive_parameter(): void
    {
        $parameter = new ReflectionParameter([OneTimeCodeMessage::class, '__construct'], 'code');

        $this->assertNotSame([], $parameter->getAttributes(SensitiveParameter::class));
    }

    public function test_a_stack_trace_does_not_carry_the_code(): void
    {
        $trace = '';

        try {
            new OneTimeCodeMessage('key', 'tenant', OneTimeCodeChannel::Sms, '+380501234567', self::CODE, '', 'Shop', 10);
        } catch (InvalidArgumentException $exception) {
            $trace = $exception->getTraceAsString() . $exception->getMessage();
        }

        $this->assertNotSame('', $trace);
        $this->assertStringNotContainsString(self::CODE, $trace);
    }

    public function test_it_refuses_a_lifetime_under_a_minute(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OneTimeCodeMessage('key', 'tenant', OneTimeCodeChannel::Sms, '+380501234567', self::CODE, 'en', 'Shop', 0);
    }

    public function test_it_refuses_an_empty_code(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OneTimeCodeMessage('key', 'tenant', OneTimeCodeChannel::Sms, '+380501234567', '', 'en', 'Shop', 10);
    }

    private function message(): OneTimeCodeMessage
    {
        return new OneTimeCodeMessage(
            idempotencyKey: 'session:node:1',
            tenantId: '01jabc',
            channel: OneTimeCodeChannel::Email,
            destination: 'client@example.com',
            code: self::CODE,
            locale: 'uk',
            brandName: 'Shop',
            expiresInMinutes: 10,
        );
    }
}
