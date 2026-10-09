<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use Fapost\Foundation\Tenancy\DTO\ReservedTenant;
use Fapost\Foundation\Tenancy\DTO\SlugCheck;
use Fapost\Foundation\Tenancy\DTO\TenantAdmin;
use Fapost\Foundation\Tenancy\Enums\SlugAvailability;
use Fapost\Foundation\Tenancy\Enums\SlugProblem;
use Fapost\Foundation\Tenancy\Enums\TenantStatus;
use Fapost\Foundation\Tenancy\Exceptions\TenantReleaseRefusedException;
use PHPUnit\Framework\TestCase;

final class TenantReservationDtoTest extends TestCase
{
    public function test_only_an_available_slug_check_is_available(): void
    {
        foreach (SlugAvailability::cases() as $availability) {
            $check = new SlugCheck('acme', $availability);

            $this->assertSame(SlugAvailability::Available === $availability, $check->isAvailable());
            $this->assertNull($check->problem);
        }
    }

    public function test_an_invalid_slug_check_carries_its_problem(): void
    {
        $check = new SlugCheck('a--b', SlugAvailability::Invalid, SlugProblem::ConsecutiveHyphens);

        $this->assertFalse($check->isAvailable());
        $this->assertSame(SlugProblem::ConsecutiveHyphens, $check->problem);
        $this->assertSame('a--b', $check->slug);
    }

    public function test_a_reserved_tenant_and_an_admin_carry_their_values(): void
    {
        $reserved = new ReservedTenant('01JABC', 'acme');
        $admin    = new TenantAdmin('admin@acme.test', 'Acme Admin', '$2y$12$hash');

        $this->assertSame('01JABC', $reserved->id);
        $this->assertSame('acme', $reserved->slug);
        $this->assertSame('admin@acme.test', $admin->email);
        $this->assertSame('Acme Admin', $admin->name);
        $this->assertSame('$2y$12$hash', $admin->passwordHash);
        $this->assertEquals($admin, unserialize(serialize($admin)));
    }

    public function test_pending_is_a_tenant_status(): void
    {
        $this->assertSame('pending', TenantStatus::Pending->value);
        $this->assertSame(TenantStatus::Pending, TenantStatus::from('pending'));
    }

    public function test_each_release_refusal_names_the_tenant(): void
    {
        foreach ([
            TenantReleaseRefusedException::notPending('01JABC'),
            TenantReleaseRefusedException::provisioningStarted('01JABC'),
            TenantReleaseRefusedException::inProgress('01JABC'),
        ] as $e) {
            $this->assertSame('01JABC', $e->tenantId);
            $this->assertStringContainsString('01JABC', $e->getMessage());
        }
    }
}
