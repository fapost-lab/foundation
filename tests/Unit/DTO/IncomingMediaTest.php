<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\DTO;

use Fapost\Foundation\DTO\IncomingMedia;
use Fapost\Foundation\Media\Enums\MediaKind;
use PHPUnit\Framework\TestCase;

final class IncomingMediaTest extends TestCase
{
    public function test_the_provider_kind_is_optional_and_defaults_to_null(): void
    {
        $media = new IncomingMedia('file-1', MediaKind::Image, 'image/jpeg', 'photo.jpg', 1024);

        $this->assertNull($media->providerKind);
    }

    public function test_it_carries_the_kind_the_connector_reported(): void
    {
        $media = new IncomingMedia(providerFileId: 'file-1', kind: MediaKind::Audio, providerKind: 'voice');

        $this->assertSame('voice', $media->providerKind);
    }
}
