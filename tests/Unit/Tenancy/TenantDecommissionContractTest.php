<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use Fapost\Foundation\Tenancy\DTO\DecommissionedTenant;
use Fapost\Foundation\Tenancy\Enums\DecommissionFailure;
use Fapost\Foundation\Tenancy\Enums\DecommissionReason;
use Fapost\Foundation\Tenancy\Events\TenantDecommissioned;
use Fapost\Foundation\Tenancy\Exceptions\TenantDecommissionFailedException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TenantDecommissionContractTest extends TestCase
{
    public function test_the_reasons_keep_their_wire_values(): void
    {
        $this->assertSame('owner_request', DecommissionReason::OwnerRequest->value);
        $this->assertSame('retention', DecommissionReason::Retention->value);
        $this->assertCount(2, DecommissionReason::cases());
    }

    public function test_the_failures_keep_their_wire_values(): void
    {
        $this->assertSame('pending', DecommissionFailure::Pending->value);
        $this->assertSame('failed', DecommissionFailure::Failed->value);
        $this->assertCount(2, DecommissionFailure::cases());
    }

    public function test_each_named_constructor_carries_its_reason_and_the_tenant(): void
    {
        $pending = TenantDecommissionFailedException::pending('01JABC');
        $failed  = TenantDecommissionFailedException::failed('01JABC');

        $this->assertSame(DecommissionFailure::Pending, $pending->reason);
        $this->assertSame(DecommissionFailure::Failed, $failed->reason);
        $this->assertSame('01JABC', $pending->tenantId);
        $this->assertSame('01JABC', $failed->tenantId);
        $this->assertInstanceOf(RuntimeException::class, $pending);
        $this->assertNotSame('', $pending->getMessage());
    }

    public function test_every_case_has_a_named_constructor(): void
    {
        $covered = [
            TenantDecommissionFailedException::pending('01JABC')->reason,
            TenantDecommissionFailedException::failed('01JABC')->reason,
        ];

        $this->assertEqualsCanonicalizing(DecommissionFailure::cases(), $covered);
    }

    public function test_the_previous_exception_is_kept_and_its_message_hidden(): void
    {
        $previous = new RuntimeException('SQLSTATE secret');
        $e        = TenantDecommissionFailedException::failed('01JABC', $previous);

        $this->assertSame($previous, $e->getPrevious());
        $this->assertStringNotContainsString('secret', $e->getMessage());
        $this->assertNull(TenantDecommissionFailedException::pending('01JABC')->getPrevious());
    }

    public function test_a_decommissioned_tenant_carries_its_values(): void
    {
        $tenant = new DecommissionedTenant('01JABC', 'acme', false, true);

        $this->assertSame('01JABC', $tenant->tenantId);
        $this->assertSame('acme', $tenant->slug);
        $this->assertFalse($tenant->alreadyGone);
        $this->assertTrue($tenant->mediaKeptInClientStorage);
    }

    public function test_an_already_gone_tenant_needs_no_slug(): void
    {
        $tenant = new DecommissionedTenant('01JABC', null, true, false);

        $this->assertNull($tenant->slug);
        $this->assertTrue($tenant->alreadyGone);
    }

    public function test_the_tenant_id_must_not_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DecommissionedTenant(' ', null, true, false);
    }

    public function test_a_tenant_that_existed_needs_its_slug(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DecommissionedTenant('01JABC', null, false, false);
    }

    public function test_a_blank_slug_is_refused_for_a_tenant_that_existed(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DecommissionedTenant('01JABC', '', false, false);
    }

    public function test_the_event_carries_its_values(): void
    {
        $event = new TenantDecommissioned('01JABC', 'acme', DecommissionReason::Retention);

        $this->assertSame('01JABC', $event->tenantId);
        $this->assertSame('acme', $event->slug);
        $this->assertSame(DecommissionReason::Retention, $event->reason);
    }
}
