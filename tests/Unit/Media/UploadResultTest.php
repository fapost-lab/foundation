<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Media;

use Fapost\Foundation\Media\DTO\UploadResult;
use PHPUnit\Framework\TestCase;

final class UploadResultTest extends TestCase
{
    public function test_the_provider_kind_is_optional_and_defaults_to_null(): void
    {
        $result = new UploadResult(providerFileId: 'file-1');

        $this->assertNull($result->providerKind);
    }

    public function test_it_carries_the_kind_the_provider_reported(): void
    {
        $result = new UploadResult(providerFileId: 'file-1', deliveredMessageId: '7', providerKind: 'document');

        $this->assertSame('document', $result->providerKind);
    }
}
