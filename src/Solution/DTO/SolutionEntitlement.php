<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\DTO;

use InvalidArgumentException;

/**
 * Which Solutions a tenant may run, as an operator package answers it: every installed one ({@see self::all()}), only
 * the listed ones ({@see self::only()}), or some granted and some allowed ({@see self::grants()}), with an optional
 * button for the tenant's admins, for example "See plans".
 *
 * A Solution is either granted or allowed. Granted ({@see SolutionGrant}): Core runs it without the tenant turning it
 * on, and the tenant cannot turn it off (a plan's bundle, a running add-on). Allowed: the tenant turns it on or off
 * itself. `all()` and `only()` allow and grant nothing.
 *
 * Ids the image does not install are allowed (a plan may bundle Solutions of another image); Core intersects the
 * answer with the Solutions it has installed.
 */
final readonly class SolutionEntitlement
{
    /**
     * @param  list<string>|null  $solutionIds  granted and allowed together, sorted and unique; null means every installed Solution
     * @param  string|null  $actionLabel  the operator's text, shown as is; given together with the URL
     * @param  string|null  $actionUrl  absolute http(s) URL, for example the operator's plans page
     * @param  array<string, SolutionGrant>  $grants  by Solution id, sorted by id; empty for `all()` and `only()`
     */
    private function __construct(
        public ?array $solutionIds,
        public ?string $actionLabel,
        public ?string $actionUrl,
        public array $grants = [],
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
        self::assertAction($actionLabel, $actionUrl);

        return new self(self::ids($solutionIds), $actionLabel, $actionUrl);
    }

    /**
     * The tenant runs the granted Solutions without turning them on, and may turn the allowed ones on itself; nothing
     * else. A Solution both granted and allowed is granted.
     *
     * @param  list<SolutionGrant>  $grants  at most one per Solution
     * @param  list<string>  $allowed  duplicates are dropped
     * @param  string|null  $actionLabel  what the tenant's admins can do about a Solution not included, given together with the URL
     * @param  string|null  $actionUrl  absolute http(s) URL the button opens
     *
     * @throws InvalidArgumentException on a value that is not a grant, two grants for one Solution, an invalid allowed id, or an invalid action (as {@see self::only()})
     */
    public static function grants(array $grants, array $allowed = [], ?string $actionLabel = null, ?string $actionUrl = null): self
    {
        self::assertAction($actionLabel, $actionUrl);

        $byId = [];

        foreach ($grants as $grant) {
            if (! $grant instanceof SolutionGrant) {
                throw new InvalidArgumentException('A grant must be a ' . SolutionGrant::class . '.');
            }

            if (isset($byId[$grant->solutionId])) {
                throw new InvalidArgumentException("The Solution \"{$grant->solutionId}\" is granted twice.");
            }

            $byId[$grant->solutionId] = $grant;
        }

        ksort($byId, SORT_STRING);

        $ids = array_values(array_unique([...self::ids($allowed), ...array_map(strval(...), array_keys($byId))]));
        sort($ids);

        return new self($ids, $actionLabel, $actionUrl, $byId);
    }

    public function allows(string $solutionId): bool
    {
        return null === $this->solutionIds || in_array($solutionId, $this->solutionIds, true);
    }

    /**
     * Whether the operator grants the Solution: Core runs it without the tenant turning it on.
     */
    public function isGranted(string $solutionId): bool
    {
        return isset($this->grants[$solutionId]);
    }

    public function grantFor(string $solutionId): ?SolutionGrant
    {
        return $this->grants[$solutionId] ?? null;
    }

    public function isUnrestricted(): bool
    {
        return null === $this->solutionIds;
    }

    /**
     * @param  list<mixed>  $solutionIds
     *
     * @return list<string> sorted and unique
     */
    private static function ids(array $solutionIds): array
    {
        $ids = [];

        foreach ($solutionIds as $id) {
            if (! is_string($id) || '' === mb_trim($id)) {
                throw new InvalidArgumentException('A Solution id in an entitlement must be a non-empty string.');
            }

            $ids[$id] = true;
        }

        $ids = array_map(strval(...), array_keys($ids));
        sort($ids);

        return $ids;
    }

    private static function assertAction(?string $actionLabel, ?string $actionUrl): void
    {
        if ((null === $actionLabel) !== (null === $actionUrl)) {
            throw new InvalidArgumentException('An entitlement action needs both a label and a URL.');
        }

        if (null !== $actionLabel && '' === mb_trim($actionLabel)) {
            throw new InvalidArgumentException('An entitlement action label cannot be empty.');
        }

        if (null !== $actionUrl && ! self::isAbsoluteHttpUrl($actionUrl)) {
            throw new InvalidArgumentException('An entitlement action URL must be an absolute http or https URL.');
        }
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
