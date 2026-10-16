<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Fapost\Foundation\Solution\DTO\SolutionEntitlement;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SolutionEntitlementTest extends TestCase
{
    /**
     * @return array<string, array{?string, ?string}>
     */
    public static function invalidActions(): array
    {
        return [
            'label without url' => ['See plans', null],
            'url without label' => [null, 'https://example.test'],
            'empty label'       => [' ', 'https://example.test'],
            'javascript url'    => ['See plans', 'javascript:alert(1)'],
            'relative url'      => ['See plans', '/plans'],
            'no host'           => ['See plans', 'https://'],
            'whitespace'        => ['See plans', 'https://example.test/a b'],
        ];
    }

    /**
     * @return array<string, array{list<mixed>}>
     */
    public static function invalidIds(): array
    {
        return [
            'empty'      => [['']],
            'whitespace' => [['  ']],
            'not string' => [[42]],
        ];
    }

    public function test_all_allows_every_solution(): void
    {
        $entitlement = SolutionEntitlement::all();

        $this->assertTrue($entitlement->isUnrestricted());
        $this->assertNull($entitlement->solutionIds);
        $this->assertTrue($entitlement->allows('anything'));
        $this->assertNull($entitlement->actionLabel);
        $this->assertNull($entitlement->actionUrl);
    }

    public function test_only_allows_the_listed_solutions_sorted_and_unique(): void
    {
        $entitlement = SolutionEntitlement::only(['hr', 'feedback', 'hr'], 'See plans', 'https://example.test/plans');

        $this->assertFalse($entitlement->isUnrestricted());
        $this->assertSame(['feedback', 'hr'], $entitlement->solutionIds);
        $this->assertTrue($entitlement->allows('hr'));
        $this->assertFalse($entitlement->allows('crm'));
        $this->assertSame('See plans', $entitlement->actionLabel);
        $this->assertSame('https://example.test/plans', $entitlement->actionUrl);
    }

    public function test_an_empty_list_allows_nothing(): void
    {
        $entitlement = SolutionEntitlement::only([]);

        $this->assertSame([], $entitlement->solutionIds);
        $this->assertFalse($entitlement->allows('hr'));
    }

    /**
     * @param  list<mixed>  $ids
     */
    #[DataProvider('invalidIds')]
    public function test_an_invalid_id_is_rejected(array $ids): void
    {
        $this->expectException(InvalidArgumentException::class);

        SolutionEntitlement::only($ids);
    }

    #[DataProvider('invalidActions')]
    public function test_an_invalid_action_is_rejected(?string $label, ?string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        SolutionEntitlement::only(['hr'], $label, $url);
    }
}
