<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Messaging;

use Fapost\Foundation\Channel\ChannelInterface;
use Fapost\Foundation\Messaging\OutboundMessage;
use Fapost\Foundation\Messaging\ProviderSenderInterface;
use Fapost\Foundation\Messaging\TypingCapableProviderInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * The envelope is queued as it is, so it must not have a place for a channel secret: the provider sender gets
 * the channel when the message is sent.
 */
final class OutboundMessageTest extends TestCase
{
    public function test_the_envelope_has_no_field_for_a_credential(): void
    {
        $properties = array_map(
            static fn (ReflectionParameter $parameter): string => $parameter->getName(),
            (new ReflectionMethod(OutboundMessage::class, '__construct'))->getParameters(),
        );

        $this->assertSame(
            ['idempotencyKey', 'tenantId', 'channelId', 'channelType', 'chatId', 'payload', 'metadata'],
            $properties,
        );

        foreach ($properties as $property) {
            $this->assertDoesNotMatchRegularExpression('/token|secret|credential|password/i', $property);
        }
    }

    public function test_the_provider_sender_gets_the_channel_when_it_delivers(): void
    {
        $this->assertSecondParameterIsTheChannel(new ReflectionMethod(ProviderSenderInterface::class, 'deliver'));
    }

    public function test_the_typing_provider_gets_the_channel_on_every_call(): void
    {
        foreach (['indicateProcessing', 'refreshProcessing', 'stopProcessing'] as $method) {
            $this->assertSecondParameterIsTheChannel(new ReflectionMethod(TypingCapableProviderInterface::class, $method));
        }
    }

    private function assertSecondParameterIsTheChannel(ReflectionMethod $method): void
    {
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters, $method->getName());
        $type = $parameters[1]->getType();
        $this->assertInstanceOf(ReflectionNamedType::class, $type);
        $this->assertSame(ChannelInterface::class, $type->getName(), $method->getName());
    }
}
