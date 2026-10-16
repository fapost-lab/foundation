<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

use Fapost\Foundation\Solution\Enums\SolutionConnectOutcome;

/**
 * What a storefront did when a tenant asked to connect a Solution.
 *
 * @experimental
 */
final readonly class SolutionConnectResult
{
    /**
     * @param  string|null  $message  the operator's text for the tenant's admins, shown as is; expected with {@see SolutionConnectOutcome::Refused}
     */
    public function __construct(
        public SolutionConnectOutcome $outcome,
        public ?string $message = null,
    ) {
    }

    public static function trialStarted(?string $message = null): self
    {
        return new self(SolutionConnectOutcome::TrialStarted, $message);
    }

    public static function requested(?string $message = null): self
    {
        return new self(SolutionConnectOutcome::Requested, $message);
    }

    public static function alreadyPending(?string $message = null): self
    {
        return new self(SolutionConnectOutcome::AlreadyPending, $message);
    }

    public static function refused(?string $message = null): self
    {
        return new self(SolutionConnectOutcome::Refused, $message);
    }
}
