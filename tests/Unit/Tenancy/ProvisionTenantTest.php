<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use Fapost\Foundation\Tenancy\DTO\ProvisionedTenant;
use Fapost\Foundation\Tenancy\DTO\ProvisionTenant;
use PHPUnit\Framework\TestCase;

final class ProvisionTenantTest extends TestCase
{
    public function test_the_request_carries_its_values(): void
    {
        $request = new ProvisionTenant('acme', 'admin@acme.test', 'Acme Admin', '$2y$12$hash');

        $this->assertSame('acme', $request->slug);
        $this->assertSame('admin@acme.test', $request->adminEmail);
        $this->assertSame('Acme Admin', $request->adminName);
        $this->assertSame('$2y$12$hash', $request->adminPasswordHash);
    }

    public function test_the_request_survives_serialization(): void
    {
        $request = new ProvisionTenant('acme', 'admin@acme.test', 'Acme Admin', '$2y$12$hash');

        $this->assertEquals($request, unserialize(serialize($request)));
    }

    public function test_a_provisioned_tenant_carries_its_values(): void
    {
        $tenant = new ProvisionedTenant('01JABCDEF', 'acme', 'https://acme.example.test/admin/login');

        $this->assertSame('01JABCDEF', $tenant->id);
        $this->assertSame('acme', $tenant->slug);
        $this->assertSame('https://acme.example.test/admin/login', $tenant->loginUrl);
    }
}
