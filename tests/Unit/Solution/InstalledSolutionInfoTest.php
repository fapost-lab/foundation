<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Fapost\Foundation\Solution\DTO\InstalledSolutionInfo;
use PHPUnit\Framework\TestCase;

final class InstalledSolutionInfoTest extends TestCase
{
    public function test_an_entry_carries_its_values(): void
    {
        $info = new InstalledSolutionInfo('feedback', 'Feedback', 'Collect feedback.', '1.2.0');

        $this->assertSame('feedback', $info->id);
        $this->assertSame('Feedback', $info->name);
        $this->assertSame('Collect feedback.', $info->description);
        $this->assertSame('1.2.0', $info->version);
    }

    public function test_description_and_version_are_optional(): void
    {
        $info = new InstalledSolutionInfo('feedback', 'Feedback');

        $this->assertNull($info->description);
        $this->assertNull($info->version);
    }
}
