<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Bundle;

/**
 * Outcome of {@see UiBundleParser::parse()}: a bundle, or every violation found.
 *
 * @experimental
 */
final readonly class UiBundleParseResult
{
    /**
     * @param  string  $solutionId  the Solution the file was read for
     * @param  list<UiBundleViolation>  $violations  empty exactly when $bundle is set
     */
    public function __construct(
        public string $solutionId,
        public ?UiBundle $bundle,
        public array $violations,
    ) {
    }

    /**
     * @experimental
     */
    public function isValid(): bool
    {
        return null !== $this->bundle;
    }

    /**
     * One line per violation, each starting with the Solution id.
     *
     * @return list<string>
     *
     * @experimental
     */
    public function lines(): array
    {
        return array_map(fn (UiBundleViolation $violation): string => $violation->describe($this->solutionId), $this->violations);
    }
}
