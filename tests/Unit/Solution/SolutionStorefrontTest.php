<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Solution;

use Fapost\Foundation\Solution\DTO\SolutionConnectResult;
use Fapost\Foundation\Solution\DTO\SolutionOffer;
use Fapost\Foundation\Solution\DTO\SolutionPrice;
use Fapost\Foundation\Solution\Enums\SolutionConnectOutcome;
use Fapost\Foundation\Solution\Enums\SolutionPricePeriod;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SolutionStorefrontTest extends TestCase
{
    /**
     * @return array<string, array{callable(): mixed}>
     */
    public static function invalidValues(): array
    {
        return [
            'negative price'      => [fn () => new SolutionPrice(-1, 'EUR')],
            'lower-case currency' => [fn () => new SolutionPrice(100, 'eur')],
            'long currency'       => [fn () => new SolutionPrice(100, 'EURO')],
            'zero period count'   => [fn () => new SolutionPrice(100, 'EUR', SolutionPricePeriod::Month, 0)],
            'offer without id'    => [fn () => new SolutionOffer(' ', 'HR')],
            'offer without name'  => [fn () => new SolutionOffer('hr', ' ')],
            'negative trial'      => [fn () => new SolutionOffer('hr', 'HR', trialDays: -1)],
        ];
    }

    public function test_an_offer_with_trial_days_starts_a_trial(): void
    {
        $offer = new SolutionOffer('hr', 'HR', 'People', 14, new SolutionPrice(900, 'EUR', SolutionPricePeriod::Year, 1), true);

        $this->assertTrue($offer->startsTrial());
        $this->assertSame(900, $offer->price?->amountMinor);
        $this->assertSame(SolutionPricePeriod::Year, $offer->price?->period);
        $this->assertTrue($offer->requestPending);
        $this->assertFalse((new SolutionOffer('hr', 'HR'))->startsTrial());
    }

    public function test_results_carry_their_outcome_and_message(): void
    {
        $this->assertSame(SolutionConnectOutcome::TrialStarted, SolutionConnectResult::trialStarted()->outcome);
        $this->assertSame(SolutionConnectOutcome::Requested, SolutionConnectResult::requested()->outcome);
        $this->assertSame(SolutionConnectOutcome::AlreadyPending, SolutionConnectResult::alreadyPending()->outcome);
        $refused = SolutionConnectResult::refused('Renew your plan first.');
        $this->assertSame(SolutionConnectOutcome::Refused, $refused->outcome);
        $this->assertSame('Renew your plan first.', $refused->message);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(callable $make): void
    {
        $this->expectException(InvalidArgumentException::class);

        $make();
    }
}
