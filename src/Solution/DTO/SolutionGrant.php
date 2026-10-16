<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

use DateTimeImmutable;
use Fapost\Foundation\Solution\Enums\SolutionGrantSource;
use InvalidArgumentException;

/**
 * One Solution an operator grants a tenant: Core runs it without the tenant turning it on, and the tenant cannot turn
 * it off. It stops when the operator's answer no longer grants it.
 */
final readonly class SolutionGrant
{
    /**
     * @param  string  $solutionId  the Solution's manifest id
     * @param  DateTimeImmutable|null  $endsAt  when the grant is expected to end (a trial, a paid period), for display only; null when it has no end or the operator does not say
     * @param  string|null  $note  the operator's text about the grant, shown as is (for example the plan's name)
     *
     * @throws InvalidArgumentException on an empty id or an empty note
     */
    public function __construct(
        public string $solutionId,
        public SolutionGrantSource $source,
        public ?DateTimeImmutable $endsAt = null,
        public ?string $note = null,
    ) {
        if ('' === mb_trim($solutionId)) {
            throw new InvalidArgumentException('A Solution id in a grant must be a non-empty string.');
        }

        if (null !== $note && '' === mb_trim($note)) {
            throw new InvalidArgumentException('A grant note cannot be empty; leave it null.');
        }
    }
}
