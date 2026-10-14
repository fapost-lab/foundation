<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Bundle;

use Fapost\Foundation\Console\ConsoleNames;

/**
 * Checks a decoded `fapost-ui.json` against {@see UiBundleSchema}.
 *
 * Pure: it reads an array and touches no file, so a Solution's CI can run it without Core. It collects every
 * violation instead of stopping at the first. What needs the installation (the listed files exist, the versions
 * satisfy Core's) is checked by the caller.
 *
 * @experimental
 */
final class UiBundleParser
{
    /**
     * @param  array<mixed>  $decoded  decoded fapost-ui.json, as `json_decode($json, true)` returns it
     * @param  string  $solutionId  the id from the manifest that references the file
     *
     * @experimental
     */
    public function parse(array $decoded, string $solutionId): UiBundleParseResult
    {
        $violations = [];

        if ([] !== $decoded && array_is_list($decoded)) {
            return new UiBundleParseResult($solutionId, null, [new UiBundleViolation('(root)', 'must be an object')]);
        }

        $schema = $this->checkSchema($decoded, $violations);

        if (null === $schema) {
            // Which rules apply is decided by the schema; judge nothing else until it is readable.
            return new UiBundleParseResult($solutionId, null, $violations);
        }

        $solution   = $this->checkSolution($decoded, $solutionId, $violations);
        $kit        = $this->checkConstraint($decoded, 'kit', $violations);
        $vue        = $this->checkConstraint($decoded, 'vue', $violations);
        $inertia    = $this->checkConstraint($decoded, 'inertia', $violations);
        $pages      = $this->checkEntries($decoded, 'pages', $violations);
        $components = $this->checkEntries($decoded, 'components', $violations);
        $css        = $this->checkCss($decoded, $violations);

        // Judged on what is listed, valid or not: a malformed entry is reported on its own, not as "nothing listed".
        if (null !== $pages && null !== $components && [] === ($decoded['pages'] ?? []) && [] === ($decoded['components'] ?? [])) {
            $violations[] = new UiBundleViolation('pages', 'pages and components together must list at least one entry');
        }

        foreach (array_keys($decoded) as $key) {
            if (!in_array($key, UiBundleSchema::FIELDS, true) && !str_starts_with((string) $key, UiBundleSchema::EXTENSION_PREFIX)) {
                $violations[] = new UiBundleViolation(
                    (string) $key,
                    sprintf(
                        'unknown field (schema %d allows: %s); a newer fapost/foundation may be required',
                        $schema,
                        implode(', ', UiBundleSchema::FIELDS),
                    ),
                );
            }
        }

        if ([] !== $violations || null === $solution || null === $kit || null === $vue || null === $inertia || null === $pages || null === $components || null === $css) {
            return new UiBundleParseResult($solutionId, null, $violations);
        }

        return new UiBundleParseResult(
            $solutionId,
            new UiBundle($schema, $solution, $kit, $vue, $inertia, $pages, $components, $css),
            [],
        );
    }

    private static function fileRule(string $extension): string
    {
        return sprintf('must be a path relative to the directory of fapost-ui.json, without ".." segments, ending with "%s"', $extension);
    }

    /**
     * @param  array<mixed>  $decoded
     * @param  list<UiBundleViolation>  $violations
     */
    private function checkSchema(array $decoded, array &$violations): ?int
    {
        $schema = $decoded['schema'] ?? null;

        if (null === $schema) {
            $violations[] = new UiBundleViolation('schema', 'required');

            return null;
        }

        if (!is_int($schema)) {
            $violations[] = new UiBundleViolation('schema', 'must be an integer');

            return null;
        }

        if (!in_array($schema, UiBundleSchema::SUPPORTED, true)) {
            $violations[] = new UiBundleViolation(
                'schema',
                sprintf('%d is not supported by this fapost/foundation (supports: %s)', $schema, implode(', ', UiBundleSchema::SUPPORTED)),
            );

            return null;
        }

        return $schema;
    }

    /**
     * @param  array<mixed>  $decoded
     * @param  list<UiBundleViolation>  $violations
     */
    private function checkSolution(array $decoded, string $solutionId, array &$violations): ?string
    {
        $solution = $decoded['solution'] ?? null;

        if (null === $solution) {
            $violations[] = new UiBundleViolation('solution', 'required');

            return null;
        }

        if ($solution !== $solutionId) {
            $violations[] = new UiBundleViolation(
                'solution',
                sprintf('must be "%s", the id in the manifest', $solutionId),
            );

            return null;
        }

        return $solution;
    }

    /**
     * @param  array<mixed>  $decoded
     * @param  list<UiBundleViolation>  $violations
     */
    private function checkConstraint(array $decoded, string $field, array &$violations): ?string
    {
        $constraint = $decoded[$field] ?? null;

        if (null === $constraint) {
            $violations[] = new UiBundleViolation($field, 'required');

            return null;
        }

        if (!is_string($constraint) || '' === mb_trim($constraint)) {
            $violations[] = new UiBundleViolation($field, 'must be a non-empty version constraint');

            return null;
        }

        return $constraint;
    }

    /**
     * @param  array<mixed>  $decoded
     * @param  list<UiBundleViolation>  $violations
     * @return array<string, string>|null  null when the field itself is malformed
     */
    private function checkEntries(array $decoded, string $field, array &$violations): ?array
    {
        if (!array_key_exists($field, $decoded)) {
            return [];
        }

        $entries = $decoded[$field];

        if (!is_array($entries) || ([] !== $entries && array_is_list($entries))) {
            $violations[] = new UiBundleViolation($field, 'must be an object of Path => file');

            return null;
        }

        $valid = [];

        foreach ($entries as $path => $file) {
            $path = (string) $path;

            if (1 !== preg_match(ConsoleNames::PATH_PATTERN, $path)) {
                $violations[] = new UiBundleViolation(
                    $field . '.' . $path,
                    'the key must be PascalCase segments joined by "/", without the "<id>::" prefix',
                );

                continue;
            }

            if (!$this->isRelativeFile($file, UiBundleSchema::SCRIPT_EXTENSION)) {
                $violations[] = new UiBundleViolation($field . '.' . $path, self::fileRule(UiBundleSchema::SCRIPT_EXTENSION));

                continue;
            }

            /** @var string $file */
            $valid[$path] = $file;
        }

        return $valid;
    }

    /**
     * @param  array<mixed>  $decoded
     * @param  list<UiBundleViolation>  $violations
     * @return list<string>|null  null when the field itself is malformed
     */
    private function checkCss(array $decoded, array &$violations): ?array
    {
        if (!array_key_exists('css', $decoded)) {
            return [];
        }

        $css = $decoded['css'];

        if (!is_array($css) || !array_is_list($css)) {
            $violations[] = new UiBundleViolation('css', 'must be a list of files');

            return null;
        }

        $valid = [];

        foreach ($css as $index => $file) {
            if (!$this->isRelativeFile($file, UiBundleSchema::STYLE_EXTENSION)) {
                $violations[] = new UiBundleViolation(sprintf('css[%d]', $index), self::fileRule(UiBundleSchema::STYLE_EXTENSION));

                continue;
            }

            /** @var string $file */
            $valid[] = $file;
        }

        return $valid;
    }

    /**
     * A path inside the bundle's directory: no leading "/", no empty, "." or ".." segment, no backslash or ":".
     */
    private function isRelativeFile(mixed $file, string $extension): bool
    {
        if (!is_string($file) || !str_ends_with($file, $extension) || mb_strlen($file) === mb_strlen($extension)) {
            return false;
        }

        if (1 === preg_match('/[\\\\:\s]/', $file)) {
            return false;
        }

        foreach (explode('/', $file) as $segment) {
            if ('' === $segment || '.' === $segment || '..' === $segment) {
                return false;
            }
        }

        return true;
    }
}
