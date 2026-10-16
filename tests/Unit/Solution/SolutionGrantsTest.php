<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use DateTimeImmutable;
use Fapost\Foundation\Solution\DTO\SolutionEntitlement;
use Fapost\Foundation\Solution\DTO\SolutionGrant;
use Fapost\Foundation\Solution\Enums\SolutionGrantSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SolutionGrantsTest extends TestCase
{
    public function test_grants_and_allowed_are_both_allowed_and_only_grants_are_granted(): void
    {
        $trialEnd    = new DateTimeImmutable('2026-11-01T00:00:00Z');
        $entitlement = SolutionEntitlement::grants(
            [new SolutionGrant('hr', SolutionGrantSource::Plan, note: 'Pro'), new SolutionGrant('crm', SolutionGrantSource::Addon, $trialEnd)],
            ['feedback', 'hr', 'feedback'],
            'See plans',
            'https://example.test/plans',
        );

        $this->assertFalse($entitlement->isUnrestricted());
        $this->assertSame(['crm', 'feedback', 'hr'], $entitlement->solutionIds);
        $this->assertSame(['crm', 'hr'], array_keys($entitlement->grants));
        $this->assertTrue($entitlement->allows('feedback'));
        $this->assertFalse($entitlement->isGranted('feedback'));
        $this->assertTrue($entitlement->isGranted('hr'));
        $this->assertSame(SolutionGrantSource::Addon, $entitlement->grantFor('crm')?->source);
        $this->assertSame($trialEnd, $entitlement->grantFor('crm')?->endsAt);
        $this->assertSame('Pro', $entitlement->grantFor('hr')?->note);
        $this->assertNull($entitlement->grantFor('feedback'));
        $this->assertFalse($entitlement->allows('other'));
        $this->assertSame('See plans', $entitlement->actionLabel);
    }

    public function test_all_and_only_grant_nothing(): void
    {
        $this->assertSame([], SolutionEntitlement::all()->grants);
        $this->assertFalse(SolutionEntitlement::all()->isGranted('hr'));
        $this->assertSame([], SolutionEntitlement::only(['hr'])->grants);
        $this->assertFalse(SolutionEntitlement::only(['hr'])->isGranted('hr'));
    }

    public function test_no_grants_and_nothing_allowed_allows_nothing(): void
    {
        $entitlement = SolutionEntitlement::grants([]);

        $this->assertSame([], $entitlement->solutionIds);
        $this->assertFalse($entitlement->allows('hr'));
    }

    public function test_a_solution_granted_twice_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SolutionEntitlement::grants([new SolutionGrant('hr', SolutionGrantSource::Plan), new SolutionGrant('hr', SolutionGrantSource::Addon)]);
    }

    public function test_a_value_that_is_not_a_grant_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore argument.type */
        SolutionEntitlement::grants(['hr']);
    }

    public function test_an_invalid_action_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SolutionEntitlement::grants([], [], 'See plans', null);
    }

    public function test_a_grant_needs_an_id_and_a_non_empty_note(): void
    {
        foreach ([fn () => new SolutionGrant(' ', SolutionGrantSource::Plan), fn () => new SolutionGrant('hr', SolutionGrantSource::Plan, note: ' ')] as $make) {
            try {
                $make();
                $this->fail('Expected an InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
