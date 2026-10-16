<?php

declare(strict_types=1);

namespace Fapost\Foundation\Connector;

use Fapost\Foundation\Channel\ChannelInboundAdapterInterface;
use Fapost\Foundation\Channel\WebhookRegistrarInterface;
use Fapost\Foundation\Media\ChannelMediaDownloaderInterface;
use Fapost\Foundation\Media\ChannelMediaLinkPolicyInterface;
use Fapost\Foundation\Media\ChannelMediaUploaderInterface;
use Fapost\Foundation\Messaging\ProviderSenderInterface;
use InvalidArgumentException;

/**
 * What a connector contributes for one channel type: the classes that serve it.
 *
 * Every service is a class name, not an instance. Core builds each service inside the request or job
 * that uses it and never shares one across requests or jobs, so a service may depend on tenant-scoped
 * bindings. Constructors must be cheap and touch no tenant data, because Core
 * builds one throwaway instance at registration to check it. The DTO itself is pure: it loads no
 * class and only rejects an empty required string.
 *
 * Since foundation 0.23, experimental.
 */
final readonly class ConnectorChannel
{
    /**
     * @param  string  $type  channel type, one of those the connector declares in `extra.fapost.channels`
     * @param  class-string<ProviderSenderInterface>  $sender  outbound delivery
     * @param  class-string<ChannelInboundAdapterInterface>  $inbound  inbound normalization and ingress spec
     * @param  class-string<WebhookRegistrarInterface>|null  $webhookRegistrar  provider-side webhook lifecycle
     * @param  class-string<ChannelMediaUploaderInterface>|null  $mediaUploader  uploads media bytes to the provider
     * @param  class-string<ChannelMediaDownloaderInterface>|null  $mediaDownloader  downloads inbound media from the provider
     * @param  class-string<ChannelMediaLinkPolicyInterface>|null  $mediaLinkPolicy  sends media by a signed public link
     *
     * @throws InvalidArgumentException when the type, the sender or the inbound class is empty
     */
    public function __construct(
        public string $type,
        public string $sender,
        public string $inbound,
        public ?string $webhookRegistrar = null,
        public ?string $mediaUploader = null,
        public ?string $mediaDownloader = null,
        public ?string $mediaLinkPolicy = null,
    ) {
        foreach (['type' => $type, 'sender' => $sender, 'inbound' => $inbound] as $field => $value) {
            if ('' === $value) {
                throw new InvalidArgumentException("A connector channel needs a non-empty {$field}.");
            }
        }
    }
}
