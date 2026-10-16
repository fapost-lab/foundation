<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

use InvalidArgumentException;

/**
 * Which Solutions a tenant may run, as an operator package answers it: every installed one ({@see self::all()}), or
 * only the listed ones ({@see self::only()}), with an optional button for the tenant's admins, for example "See plans".
 *
 * Ids the image does not install are allowed (a plan may bundle Solutions of another image); Core intersects the
 * answer with the Solutions it has installed.
 */
final readonly class SolutionEntitlement
{
    /**
     * @param  list<string>|null  $solutionIds  sorted and unique; null means every installed Solution
     * @param  string|null  $actionLabel  the operator's text, shown as is; given together with the URL
     * @param  string|null  $actionUrl  absolute http(s) URL, for example the operator's plans page
     */
    private function __construct(
        public ?array $solutionIds,
        public ?string $actionLabel,
        public ?string $actionUrl,
    ) {
    }

    /**
     * Nothing is restricted: the tenant may run every installed Solution.
     */
    public static function all(): self
    {
        return new self(null, null, null);
    }

    /**
     * The tenant may run only these Solutions; an empty list means none.
     *
     * @param  list<string>  $solutionIds  duplicates are dropped
     * @param  string|null  $actionLabel  what the tenant's admins can do about a Solution not included, given together with the URL
     * @param  string|null  $actionUrl  absolute http(s) URL the button opens
     *
     * @throws InvalidArgumentException on an id that is not a non-empty string, an action with only a label or only a URL, an empty label, or a URL that is not absolute http(s)
     */
    public static function only(array $solutionIds, ?string $actionLabel = null, ?string $actionUrl = null): self
    {
        $ids = [];

        foreach ($solutionIds as $id) {
            if (! is_string($id) || '' === mb_trim($id)) {
                throw new InvalidArgumentException('A Solution id in an entitlement must be a non-empty string.');
            }

            $ids[$id] = true;
        }

        $ids = array_keys($ids);
        sort($ids);

        if ((null === $actionLabel) !== (null === $actionUrl)) {
            throw new InvalidArgumentException('An entitlement action needs both a label and a URL.');
        }

        if (null !== $actionLabel && '' === mb_trim($actionLabel)) {
            throw new InvalidArgumentException('An entitlement action label cannot be empty.');
        }

        if (null !== $actionUrl && ! self::isAbsoluteHttpUrl($actionUrl)) {
            throw new InvalidArgumentException('An entitlement action URL must be an absolute http or https URL.');
        }

        return new self($ids, $actionLabel, $actionUrl);
    }

    public function allows(string $solutionId): bool
    {
        return null === $this->solutionIds || in_array($solutionId, $this->solutionIds, true);
    }

    public function isUnrestricted(): bool
    {
        return null === $this->solutionIds;
    }

    private static function isAbsoluteHttpUrl(string $url): bool
    {
        if (1 === preg_match('/[\s\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        $parts = parse_url($url);

        return false !== $parts
            && in_array(mb_strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && '' !== ($parts['host'] ?? '');
    }
}
