<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use Fapost\Foundation\Tenancy\Enums\ProvisioningFailure;
use Fapost\Foundation\Tenancy\Exceptions\TenantProvisioningFailedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TenantProvisioningFailedExceptionTest extends TestCase
{
    /**
     * @return array<string, array{TenantProvisioningFailedException, ProvisioningFailure}>
     */
    public static function failures(): array
    {
        return [
            'invalid'  => [TenantProvisioningFailedException::slugInvalid('A b'), ProvisioningFailure::SlugInvalid],
            'reserved' => [TenantProvisioningFailedException::slugReserved('admin'), ProvisioningFailure::SlugReserved],
            'taken'    => [TenantProvisioningFailedException::slugTaken('acme'), ProvisioningFailure::SlugTaken],
            'creds'    => [TenantProvisioningFailedException::adminCredentialsMissing(), ProvisioningFailure::AdminCredentialsMissing],
            'hash'     => [TenantProvisioningFailedException::adminPasswordHashInvalid(), ProvisioningFailure::AdminPasswordHashInvalid],
            'failed'   => [TenantProvisioningFailedException::failed('acme'), ProvisioningFailure::Failed],
        ];
    }

    #[DataProvider('failures')]
    public function test_each_named_constructor_carries_its_reason(TenantProvisioningFailedException $e, ProvisioningFailure $reason): void
    {
        $this->assertSame($reason, $e->reason);
        $this->assertInstanceOf(RuntimeException::class, $e);
        $this->assertNotSame('', $e->getMessage());
    }

    public function test_the_previous_exception_is_kept(): void
    {
        $previous = new RuntimeException('step failed');

        $this->assertSame($previous, TenantProvisioningFailedException::failed('acme', $previous)->getPrevious());
    }

    public function test_only_failed_is_not_an_input_error(): void
    {
        $this->assertTrue(ProvisioningFailure::AdminPasswordHashInvalid->isInputError());
        $this->assertFalse(ProvisioningFailure::Failed->isInputError());
    }
}
