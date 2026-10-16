<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Settings;

use Fapost\Foundation\Settings\DTO\PlatformSettingValue;
use Fapost\Foundation\Settings\Exceptions\InvalidPlatformSettingsException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InvalidPlatformSettingsExceptionTest extends TestCase
{
    public function test_it_names_every_refused_key(): void
    {
        $exception = InvalidPlatformSettingsException::because([
            'flow.execution.max_iterations' => 'must be at least 10.',
            'unknown.key'                   => 'is not a platform setting.',
        ]);

        $this->assertSame([
            'flow.execution.max_iterations' => 'must be at least 10.',
            'unknown.key'                   => 'is not a platform setting.',
        ], $exception->violations);
        $this->assertSame('flow.execution.max_iterations: must be at least 10. unknown.key: is not a platform setting.', $exception->getMessage());
    }

    public function test_it_needs_a_violation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InvalidPlatformSettingsException::because([]);
    }

    public function test_a_value_carries_where_it_comes_from(): void
    {
        $value = new PlatformSettingValue('tenancy.rename.redirect_days', 14, 30, true);

        $this->assertSame('tenancy.rename.redirect_days', $value->key);
        $this->assertSame(14, $value->value);
        $this->assertSame(30, $value->default);
        $this->assertTrue($value->stored);
    }
}
