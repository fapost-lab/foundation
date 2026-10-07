<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use DateTimeImmutable;
use Fapost\Foundation\Tenancy\DTO\SupportAccessGrant;
use Fapost\Foundation\Tenancy\DTO\SupportAccessRequest;
use Fapost\Foundation\Tenancy\Exceptions\SupportAccessUnavailableException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SupportAccessDtoTest extends TestCase
{
    public function test_a_request_carries_its_values(): void
    {
        $request = new SupportAccessRequest('019e081a-1c4e-2618-2a46-275dda69f357', 'operator:1', 'Olga', 'ops@example.com');

        $this->assertSame('019e081a-1c4e-2618-2a46-275dda69f357', $request->tenantId);
        $this->assertSame('operator:1', $request->operatorRef);
        $this->assertSame('Olga', $request->operatorName);
        $this->assertSame('ops@example.com', $request->operatorEmail);
    }

    public function test_a_request_refuses_an_empty_field(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SupportAccessRequest('019e081a-1c4e-2618-2a46-275dda69f357', 'operator:1', ' ', 'ops@example.com');
    }

    public function test_a_grant_hides_its_token_from_dumps(): void
    {
        $grant = new SupportAccessGrant('https://acme.example.test/support/enter', 'secret-token', new DateTimeImmutable('2026-10-07T12:00:00Z'));

        $this->assertSame('secret-token', $grant->token);
        $this->assertStringNotContainsString('secret-token', print_r($grant, true));
    }

    public function test_the_exception_names_its_reason(): void
    {
        $this->assertSame('Support access is not enabled on this platform.', SupportAccessUnavailableException::disabled()->getMessage());
    }
}
