<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

use InvalidArgumentException;

/**
 * A Solution an operator offers a tenant to connect, as its storefront answers it.
 *
 * @experimental
 */
final readonly class SolutionOffer
{
    /**
     * @param  string  $solutionId  the Solution's manifest id
     * @param  string  $name  the offer's name, shown as is
     * @param  int  $trialDays  more than zero when connecting starts a trial of this many days; zero when it sends a request
     * @param  SolutionPrice|null  $price  null when the operator quotes none
     * @param  bool  $requestPending  a request from this tenant already waits for the operator
     *
     * @throws InvalidArgumentException on an empty id or name, or negative trial days
     */
    public function __construct(
        public string $solutionId,
        public string $name,
        public ?string $description = null,
        public int $trialDays = 0,
        public ?SolutionPrice $price = null,
        public bool $requestPending = false,
    ) {
        if ('' === mb_trim($solutionId)) {
            throw new InvalidArgumentException('An offer needs a Solution id.');
        }

        if ('' === mb_trim($name)) {
            throw new InvalidArgumentException('An offer needs a name.');
        }

        if ($trialDays < 0) {
            throw new InvalidArgumentException('Trial days cannot be negative.');
        }
    }

    public function startsTrial(): bool
    {
        return $this->trialDays > 0;
    }
}
