<?php

declare(strict_types=1);

namespace Fapost\Foundation\Channel\Ingress;

use JsonSerializable;
use ValueError;

/**
 * A provider's endpoint-ownership challenge (Slack's `url_verification`): when the `when` template resolves to
 * exactly `equals`, the runtime answers with the resolved `respond` template and neither deduplicates nor enqueues.
 *
 * @experimental Since foundation 0.33.
 */
final readonly class IngressHandshake implements JsonSerializable
{
    /**
     * @param  string  $when     template resolved against the request document
     * @param  string  $equals   the exact, case-sensitive value that marks a challenge
     * @param  string  $respond  template whose result is the plain-text answer
     *
     * @throws ValueError when any part is empty
     */
    public function __construct(
        public string $when,
        public string $equals,
        public string $respond,
    ) {
        if ('' === $when || '' === $equals || '' === $respond) {
            throw new ValueError('An ingress handshake needs a "when" template, an "equals" value and a "respond" template.');
        }
    }

    /**
     * @return array{when: string, equals: string, respond: string}
     */
    public function jsonSerialize(): array
    {
        return ['when' => $this->when, 'equals' => $this->equals, 'respond' => $this->respond];
    }
}
