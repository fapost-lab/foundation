<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Ingress;

use JsonSerializable;
use ValueError;

/**
 * Request-age check of an {@see IngressSpec}: the header carries the unix time the provider signed the request at,
 * and a request further from "now" than the tolerance (either way) is refused as a replay.
 *
 * @experimental Since foundation 0.33.
 */
final readonly class IngressTimestamp implements JsonSerializable
{
    public const int MAX_TOLERANCE_SECONDS = 3600;

    /**
     * @param  string  $header            name of the header carrying the unix timestamp in seconds
     * @param  int     $toleranceSeconds  allowed distance from the current time, 1..3600
     *
     * @throws ValueError when the header is empty or the tolerance is out of range
     */
    public function __construct(
        public string $header,
        public int $toleranceSeconds,
    ) {
        if ('' === $header) {
            throw new ValueError('An ingress timestamp needs a header name.');
        }

        if ($toleranceSeconds < 1 || $toleranceSeconds > self::MAX_TOLERANCE_SECONDS) {
            throw new ValueError('The ingress timestamp tolerance must be between 1 and ' . self::MAX_TOLERANCE_SECONDS . ' seconds.');
        }
    }

    /**
     * @return array{header: string, tolerance: int}
     */
    public function jsonSerialize(): array
    {
        return ['header' => $this->header, 'tolerance' => $this->toleranceSeconds];
    }
}
