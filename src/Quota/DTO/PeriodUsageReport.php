<?php

declare(strict_types=1);

namespace Fapost\Foundation\Quota\DTO;

use InvalidArgumentException;

/**
 * What an operator reports about a tenant's per-period usage: one entry per key, and optionally one
 * button for the people reading it, for example "Upgrade plan".
 */
final readonly class PeriodUsageReport
{
    /**
     * @param  list<PeriodUsage>  $usage  one entry per key the operator reports; a key not listed is "not reported"
     * @param  string|null  $actionLabel  in the requested locale, or English; given together with the URL
     * @param  string|null  $actionUrl  absolute http(s) URL, for example the operator's upgrade page
     *
     * @throws InvalidArgumentException on an entry that is not a PeriodUsage, a duplicate key, an action with only a label or only a URL, or a URL that is not http(s)
     */
    public function __construct(
        public array $usage = [],
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {
        $seen = [];

        foreach ($usage as $entry) {
            if (! $entry instanceof PeriodUsage) {
                throw new InvalidArgumentException('A period usage report holds PeriodUsage entries only.');
            }

            if (isset($seen[$entry->key])) {
                throw new InvalidArgumentException(sprintf('The key "%s" is reported twice.', $entry->key));
            }

            $seen[$entry->key] = true;
        }

        if ((null === $actionLabel) !== (null === $actionUrl)) {
            throw new InvalidArgumentException('A report action needs both a label and a URL.');
        }

        if (null !== $actionLabel && '' === mb_trim($actionLabel)) {
            throw new InvalidArgumentException('A report action label cannot be empty.');
        }

        if (null !== $actionUrl && ! self::isAbsoluteHttpUrl($actionUrl)) {
            throw new InvalidArgumentException('A report action URL must be an absolute http or https URL.');
        }
    }

    public function find(string $key): ?PeriodUsage
    {
        foreach ($this->usage as $entry) {
            if ($entry->key === $key) {
                return $entry;
            }
        }

        return null;
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
