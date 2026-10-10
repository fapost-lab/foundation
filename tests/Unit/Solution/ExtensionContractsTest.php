<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Fapost\Foundation\Contracts\ActivatableInterface;
use Fapost\Foundation\Contracts\CoreRegistrarInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ExtensionContractsTest extends TestCase
{
    public function test_identity_is_data_not_a_provider_method(): void
    {
        $this->assertSame(
            ['onActivate', 'onDeactivate'],
            array_map(static fn ($method): string => $method->getName(), (new ReflectionClass(ActivatableInterface::class))->getMethods()),
        );
        $this->assertFalse(method_exists(CoreRegistrarInterface::class, 'registerManifest'));
    }
}
