<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Connector;

use Fapost\Foundation\Connector\ConnectorChannel;
use Fapost\Foundation\Connector\ConnectorRegistrarInterface;
use Fapost\Foundation\Lifecycle\AbstractConnectorServiceProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AbstractConnectorServiceProviderTest extends TestCase
{
    public function test_register_channels_with_hands_the_registrar_to_the_provider(): void
    {
        $registrar = new class () implements ConnectorRegistrarInterface {
            /** @var list<ConnectorChannel> */
            public array $channels = [];

            public function registerChannel(ConnectorChannel $channel): void
            {
                $this->channels[] = $channel;
            }
        };

        $provider = new class (new stdClass()) extends AbstractConnectorServiceProvider {
            protected function registerChannels(ConnectorRegistrarInterface $registrar): void
            {
                $registrar->registerChannel(new ConnectorChannel('acme', 'Acme\\Sender', 'Acme\\Inbound'));
            }
        };

        $provider->registerChannelsWith($registrar);

        $this->assertCount(1, $registrar->channels);
        $this->assertSame('acme', $registrar->channels[0]->type);
    }
}
