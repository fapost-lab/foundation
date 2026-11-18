<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Media;

use Fapost\Foundation\Channel\ChannelInterface;
use Fapost\Foundation\Media\ChannelMediaUploaderInterface;
use Fapost\Foundation\Media\DTO\UploadContext;
use Fapost\Foundation\Media\DTO\UploadResult;
use Fapost\Foundation\Media\MediaBlobReadInterface;
use Fapost\Foundation\Media\ReportsProviderKind;
use PHPUnit\Framework\TestCase;

final class ReportsProviderKindTest extends TestCase
{
    public function test_an_uploader_declares_that_it_reports_the_provider_kind_by_implementing_the_marker(): void
    {
        $reporting = new class () implements ChannelMediaUploaderInterface, ReportsProviderKind {
            public function channelType(): string
            {
                return 'demo';
            }

            public function upload(MediaBlobReadInterface $blob, ChannelInterface $channel, ?UploadContext $context = null): UploadResult
            {
                return new UploadResult('id', providerKind: 'document');
            }
        };

        $plain = new class () implements ChannelMediaUploaderInterface {
            public function channelType(): string
            {
                return 'demo';
            }

            public function upload(MediaBlobReadInterface $blob, ChannelInterface $channel, ?UploadContext $context = null): UploadResult
            {
                return new UploadResult('id');
            }
        };

        $this->assertInstanceOf(ReportsProviderKind::class, $reporting);
        $this->assertNotInstanceOf(ReportsProviderKind::class, $plain);
    }
}
