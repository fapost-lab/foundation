<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use Fapost\Foundation\Tenancy\Enums\ProvisioningFailure;
use Fapost\Foundation\Tenancy\Exceptions\TenantProvisioningFailedException;

use function in_array;

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
            'missing'  => [TenantProvisioningFailedException::tenantNotFound('01JABC'), ProvisioningFailure::TenantNotFound],
            'pending'  => [TenantProvisioningFailedException::tenantNotPending('01JABC'), ProvisioningFailure::TenantNotPending],
            'conflict' => [TenantProvisioningFailedException::conflict('01JABC'), ProvisioningFailure::Conflict],
            'busy'     => [TenantProvisioningFailedException::inProgress('01JABC'), ProvisioningFailure::InProgress],
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

    public function test_only_platform_failures_are_not_input_errors(): void
    {
        $this->assertTrue(ProvisioningFailure::AdminPasswordHashInvalid->isInputError());
        $this->assertTrue(ProvisioningFailure::TenantNotFound->isInputError());
        $this->assertTrue(ProvisioningFailure::TenantNotPending->isInputError());
        $this->assertFalse(ProvisioningFailure::Failed->isInputError());
        $this->assertFalse(ProvisioningFailure::InProgress->isInputError());
        $this->assertFalse(ProvisioningFailure::Conflict->isInputError());
    }

    public function test_only_failed_and_in_progress_are_retryable(): void
    {
        foreach (ProvisioningFailure::cases() as $case) {
            $expected = in_array($case, [ProvisioningFailure::Failed, ProvisioningFailure::InProgress], true);

            $this->assertSame($expected, $case->isRetryable(), $case->value);
        }
    }

    public function test_the_tenant_id_is_optional_and_set_for_failed_and_in_progress(): void
    {
        $this->assertNull(TenantProvisioningFailedException::failed('acme')->tenantId);
        $this->assertNull(TenantProvisioningFailedException::slugTaken('acme')->tenantId);
        $this->assertSame('01JABC', TenantProvisioningFailedException::failed('acme', null, '01JABC')->tenantId);
        $this->assertSame('01JABC', TenantProvisioningFailedException::inProgress('01JABC')->tenantId);
        $this->assertSame('01JABC', TenantProvisioningFailedException::conflict('01JABC')->tenantId);
    }
}
