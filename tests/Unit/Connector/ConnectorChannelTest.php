<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Connector;

use Fapost\Foundation\Connector\ConnectorChannel;
use Fapost\Foundation\Messaging\ChannelCapabilities;
use Fapost\Foundation\Messaging\ChannelCapability;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConnectorChannelTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function emptyRequired(): array
    {
        return [
            'type'    => ['', 'Acme\\Sender', 'Acme\\Inbound'],
            'sender'  => ['acme', '', 'Acme\\Inbound'],
            'inbound' => ['acme', 'Acme\\Sender', ''],
        ];
    }
    public function test_it_keeps_its_values(): void
    {
        $channel = new ConnectorChannel(
            type: 'acme',
            sender: 'Acme\\Sender',
            inbound: 'Acme\\Inbound',
            webhookRegistrar: 'Acme\\Webhook',
            mediaUploader: 'Acme\\Uploader',
            mediaDownloader: 'Acme\\Downloader',
            mediaLinkPolicy: 'Acme\\LinkPolicy',
            profile: 'Acme\\Profile',
            mediaLimits: 'Acme\\Limits',
        );

        $this->assertSame('acme', $channel->type);
        $this->assertSame('Acme\\Sender', $channel->sender);
        $this->assertSame('Acme\\Inbound', $channel->inbound);
        $this->assertSame('Acme\\Webhook', $channel->webhookRegistrar);
        $this->assertSame('Acme\\Uploader', $channel->mediaUploader);
        $this->assertSame('Acme\\Downloader', $channel->mediaDownloader);
        $this->assertSame('Acme\\LinkPolicy', $channel->mediaLinkPolicy);
        $this->assertSame('Acme\\Profile', $channel->profile);
        $this->assertSame('Acme\\Limits', $channel->mediaLimits);
    }

    public function test_optional_services_default_to_null(): void
    {
        $channel = new ConnectorChannel('acme', 'Acme\\Sender', 'Acme\\Inbound');

        $this->assertNull($channel->webhookRegistrar);
        $this->assertNull($channel->mediaUploader);
        $this->assertNull($channel->mediaDownloader);
        $this->assertNull($channel->mediaLinkPolicy);
        $this->assertNull($channel->profile);
        $this->assertNull($channel->mediaLimits);
    }

    public function test_it_declares_no_capabilities_by_default(): void
    {
        $channel = new ConnectorChannel('acme', 'Acme\\Sender', 'Acme\\Inbound');

        $this->assertSame([], $channel->capabilities->features);
    }

    public function test_it_keeps_the_capabilities_it_declares(): void
    {
        $channel = new ConnectorChannel(
            type: 'acme',
            sender: 'Acme\\Sender',
            inbound: 'Acme\\Inbound',
            capabilities: new ChannelCapabilities([ChannelCapability::CallbackButtons]),
        );

        $this->assertTrue($channel->capabilities->supports(ChannelCapability::CallbackButtons));
    }

    #[DataProvider('emptyRequired')]
    public function test_it_rejects_an_empty_required_string(string $type, string $sender, string $inbound): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConnectorChannel($type, $sender, $inbound);
    }
}
