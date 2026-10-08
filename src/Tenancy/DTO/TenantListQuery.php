<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\DTO;

use Fapost\Foundation\Tenancy\Enums\TenantSort;
use Fapost\Foundation\Tenancy\Enums\TenantStatus;
use InvalidArgumentException;

/**
 * What a caller asks a tenant directory for: filters, order and page.
 */
final readonly class TenantListQuery
{
    public const int MAX_PER_PAGE = 100;

    /** The most ids `onlyIds` or `exceptIds` may carry; a longer list is a sign the filter belongs elsewhere. */
    public const int MAX_IDS = 1000;

    /**
     * @param  string|null  $search  a substring of the slug, matched case-insensitively
     * @param  list<string>|null  $onlyIds  only these tenants; null means no restriction, an empty list matches nothing
     * @param  list<string>|null  $exceptIds  never these tenants; ids that name no tenant are ignored
     *
     * @throws InvalidArgumentException when the page or page size is out of range, or an id list is too long or not strings
     */
    public function __construct(
        public ?string $search = null,
        public ?TenantStatus $status = null,
        public ?array $onlyIds = null,
        public ?array $exceptIds = null,
        public TenantSort $sort = TenantSort::Slug,
        public bool $descending = false,
        public int $page = 1,
        public int $perPage = 25,
    ) {
        if ($page < 1) {
            throw new InvalidArgumentException('The page must be at least 1.');
        }

        if ($perPage < 1 || $perPage > self::MAX_PER_PAGE) {
            throw new InvalidArgumentException(sprintf('The page size must be between 1 and %d.', self::MAX_PER_PAGE));
        }

        self::assertIds('onlyIds', $onlyIds);
        self::assertIds('exceptIds', $exceptIds);
    }

    /**
     * @param  array<mixed>|null  $ids
     */
    private static function assertIds(string $name, ?array $ids): void
    {
        if ($ids === null) {
            return;
        }

        if (count($ids) > self::MAX_IDS) {
            throw new InvalidArgumentException(sprintf('%s may carry at most %d ids.', $name, self::MAX_IDS));
        }

        foreach ($ids as $id) {
            if (! is_string($id)) {
                throw new InvalidArgumentException(sprintf('%s must be a list of strings.', $name));
            }
        }
    }
}
