<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use DateTimeImmutable;
use Fapost\Foundation\Tenancy\DTO\TenantList;
use Fapost\Foundation\Tenancy\DTO\TenantListQuery;
use Fapost\Foundation\Tenancy\DTO\TenantSummary;
use Fapost\Foundation\Tenancy\Enums\TenantSort;
use Fapost\Foundation\Tenancy\Enums\TenantStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TenantDirectoryDtoTest extends TestCase
{
    public function test_a_query_defaults_to_the_first_page_by_slug(): void
    {
        $query = new TenantListQuery();

        $this->assertNull($query->search);
        $this->assertNull($query->status);
        $this->assertNull($query->onlyIds);
        $this->assertNull($query->exceptIds);
        $this->assertSame(TenantSort::Slug, $query->sort);
        $this->assertFalse($query->descending);
        $this->assertSame(1, $query->page);
        $this->assertSame(25, $query->perPage);
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function invalidPages(): array
    {
        return [
            'page zero'       => [0, 25],
            'page size zero'  => [1, 0],
            'page size above' => [1, TenantListQuery::MAX_PER_PAGE + 1],
        ];
    }

    #[DataProvider('invalidPages')]
    public function test_a_query_refuses_an_out_of_range_page(int $page, int $perPage): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TenantListQuery(page: $page, perPage: $perPage);
    }

    public function test_a_summary_and_a_list_carry_their_values(): void
    {
        $createdAt = new DateTimeImmutable('2026-10-06T12:00:00Z');
        $summary   = new TenantSummary('01JABCDEF', 'acme', TenantStatus::Active, $createdAt, 'https://acme.example.test/', 'https://acme.example.test/admin/login');
        $list      = new TenantList([$summary], 41, 2, 20);

        $this->assertSame('01JABCDEF', $summary->id);
        $this->assertSame('acme', $summary->slug);
        $this->assertSame(TenantStatus::Active, $summary->status);
        $this->assertSame($createdAt, $summary->createdAt);
        $this->assertSame('https://acme.example.test/', $summary->url);
        $this->assertSame('https://acme.example.test/admin/login', $summary->adminLoginUrl);
        $this->assertSame([$summary], $list->items);
        $this->assertSame(41, $list->total);
        $this->assertSame(2, $list->page);
        $this->assertSame(20, $list->perPage);
    }

    public function test_the_status_values_match_the_platform_record(): void
    {
        $this->assertSame(['active', 'inactive', 'suspended'], array_map(static fn (TenantStatus $s): string => $s->value, TenantStatus::cases()));
    }
}
