<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use DateTimeImmutable;
use Fapost\Foundation\Tenancy\DTO\RenamedTenant;
use Fapost\Foundation\Tenancy\Enums\RenameFailure;
use Fapost\Foundation\Tenancy\Enums\SlugProblem;
use Fapost\Foundation\Tenancy\Exceptions\TenantRenameFailedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TenantRenameContractTest extends TestCase
{
    /**
     * @return array<string, array{TenantRenameFailedException, RenameFailure}>
     */
    public static function failures(): array
    {
        return [
            'invalid'     => [TenantRenameFailedException::slugInvalid('01JABC', 'A b', SlugProblem::Malformed), RenameFailure::SlugInvalid],
            'reserved'    => [TenantRenameFailedException::slugReserved('01JABC', 'admin'), RenameFailure::SlugReserved],
            'taken'       => [TenantRenameFailedException::slugTaken('01JABC', 'acme'), RenameFailure::SlugTaken],
            'missing'     => [TenantRenameFailedException::tenantNotFound('01JABC'), RenameFailure::TenantNotFound],
            'pending'     => [TenantRenameFailedException::tenantPending('01JABC'), RenameFailure::TenantPending],
            'unavailable' => [TenantRenameFailedException::unavailable('01JABC'), RenameFailure::Unavailable],
            'failed'      => [TenantRenameFailedException::failed('01JABC'), RenameFailure::Failed],
        ];
    }

    #[DataProvider('failures')]
    public function test_each_named_constructor_carries_its_reason_and_the_tenant(TenantRenameFailedException $e, RenameFailure $reason): void
    {
        $this->assertSame($reason, $e->reason);
        $this->assertSame('01JABC', $e->tenantId);
        $this->assertInstanceOf(RuntimeException::class, $e);
        $this->assertNotSame('', $e->getMessage());
    }

    public function test_every_case_has_a_named_constructor(): void
    {
        $covered = array_map(static fn (array $row): RenameFailure => $row[1], self::failures());

        $this->assertEqualsCanonicalizing(RenameFailure::cases(), array_values($covered));
    }

    public function test_only_an_invalid_slug_carries_a_problem(): void
    {
        $this->assertSame(SlugProblem::TooLong, TenantRenameFailedException::slugInvalid('01JABC', 'x', SlugProblem::TooLong)->problem);
        $this->assertNull(TenantRenameFailedException::slugInvalid('01JABC', 'x')->problem);
        $this->assertNull(TenantRenameFailedException::slugTaken('01JABC', 'x')->problem);
    }

    public function test_the_previous_exception_is_kept(): void
    {
        $previous = new RuntimeException('connection lost');

        $this->assertSame($previous, TenantRenameFailedException::failed('01JABC', $previous)->getPrevious());
    }

    public function test_the_top_level_message_hides_the_cause(): void
    {
        $e = TenantRenameFailedException::failed('01JABC', new RuntimeException('SQLSTATE secret'));

        $this->assertStringNotContainsString('secret', $e->getMessage());
    }

    public function test_only_a_platform_failure_is_not_an_input_error(): void
    {
        foreach (RenameFailure::cases() as $case) {
            $this->assertSame(RenameFailure::Failed !== $case, $case->isInputError(), $case->value);
        }
    }

    public function test_a_renamed_tenant_carries_its_values(): void
    {
        $until   = new DateTimeImmutable('2026-11-01T00:00:00+00:00');
        $renamed = new RenamedTenant('01JABC', 'acme2', 'acme', true, 'https://acme2.example.test/', 'https://acme2.example.test/admin/login', $until);

        $this->assertTrue($renamed->changed);
        $this->assertSame('acme', $renamed->previousSlug);
        $this->assertSame($until, $renamed->redirectUntil);
        $this->assertNull((new RenamedTenant('01JABC', 'acme', 'acme', false, 'u', 'l'))->redirectUntil);
    }
}
