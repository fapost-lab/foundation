<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

use Fapost\Foundation\Manifest\Internal\ManifestRules;
use Fapost\Foundation\Support\PackagePath;

/**
 * Checks a decoded `composer.json` against the Solution manifest schema.
 *
 * Pure: it reads an array, touches no file, loads no class and runs no package code, so a
 * Solution's CI can run it without Core. It collects every violation instead of stopping at the
 * first, so an author fixes a manifest in one pass. What needs the installation (the provider
 * class exists, ids are unique across packages) is checked by the caller.
 */
final class ManifestParser
{
    private const string ACTION_TAIL = '[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*';

    private readonly ManifestRules $rules;

    public function __construct()
    {
        $this->rules = new ManifestRules('Solution', ManifestSchema::SUPPORTED);
    }

    /**
     * @param  array<string, mixed>  $composer  decoded composer.json of the package
     */
    public function parse(array $composer): ManifestParseResult
    {
        $package    = is_string($composer['name'] ?? null) && '' !== $composer['name'] ? $composer['name'] : '(unnamed package)';
        $extra      = $composer['extra'] ?? null;
        $block      = is_array($extra) ? ($extra['fapost'] ?? null) : null;
        $violations = [];

        if (is_array($block) && !(array_is_list($block) && [] !== $block)) {
            /** @var array<string, mixed> $block */
            $schema = $this->rules->checkSchema($block, $violations);

            if (null === $schema) {
                // Which rules apply is decided by the schema; judge nothing else until it is readable.
                return new ManifestParseResult($package, null, $violations);
            }
        }

        if (!is_string($composer['name'] ?? null) || '' === $composer['name']) {
            $violations[] = new ManifestViolation('name', 'required');
        }

        if (ManifestSchema::PACKAGE_TYPE !== ($composer['type'] ?? null)) {
            $violations[] = new ManifestViolation('type', sprintf('must be "%s"', ManifestSchema::PACKAGE_TYPE));
        }

        $violations = [...$violations, ...$this->rules->checkRequire($composer), ...$this->rules->checkLaravelProviders($composer)];

        if (!is_array($block) || array_is_list($block) && [] !== $block) {
            $violations[] = new ManifestViolation('extra.fapost', 'required: an object with the Solution manifest');

            return new ManifestParseResult($package, null, $violations);
        }

        /** @var array<string, mixed> $block */
        $schema = $block['schema'];

        $id          = $this->rules->checkId($block, $violations);
        $name        = $this->rules->checkName($block, $violations);
        $description = $this->rules->checkDescription($block, $violations);
        $provider    = $this->rules->checkProvider($block, $violations);
        $actions     = $this->checkActions($block, $id, $violations);
        $ui          = $this->checkUi($block, $violations);

        foreach (array_keys($block) as $key) {
            if (!in_array($key, ManifestSchema::FIELDS, true) && !str_starts_with((string) $key, ManifestSchema::EXTENSION_PREFIX)) {
                $violations[] = new ManifestViolation(
                    'extra.fapost.' . $key,
                    sprintf(
                        'unknown field (schema %d allows: %s); a newer fapost/foundation may be required',
                        $schema,
                        implode(', ', ManifestSchema::FIELDS),
                    ),
                );
            }
        }

        $constraint = $this->rules->foundationConstraint($composer);

        if ([] !== $violations || null === $id || null === $name || null === $provider || null === $constraint) {
            return new ManifestParseResult($package, null, $violations);
        }

        return new ManifestParseResult(
            $package,
            new SolutionManifest($package, $schema, $id, $name, $description, $provider, $actions, $constraint, $ui),
            [],
        );
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     * @return list<string>
     */
    private function checkActions(array $block, ?string $id, array &$violations): array
    {
        if (!array_key_exists('actions', $block)) {
            return [];
        }

        $actions = $block['actions'];

        if (!is_array($actions) || !array_is_list($actions)) {
            $violations[] = new ManifestViolation('extra.fapost.actions', 'must be a list of action ids');

            return [];
        }

        $valid = [];
        $seen  = [];

        foreach ($actions as $index => $action) {
            $field = sprintf('extra.fapost.actions[%d]', $index);

            if (!is_string($action)) {
                $violations[] = new ManifestViolation($field, 'must be a string');

                continue;
            }

            if (null !== $id && !str_starts_with($action, $id . '.')) {
                $violations[] = new ManifestViolation($field, sprintf('"%s" must start with "%s."', $action, $id));

                continue;
            }

            $pattern = '/^' . (null === $id ? '[a-z][a-z0-9_]{1,31}' : preg_quote($id, '/')) . '\.' . self::ACTION_TAIL . '$/D';

            if (1 !== preg_match($pattern, $action)) {
                $violations[] = new ManifestViolation(
                    $field,
                    sprintf('"%s" must be dot-separated lowercase words (letters, digits, underscore) after "%s."', $action, $id ?? '<id>'),
                );

                continue;
            }

            if (isset($seen[$action])) {
                $violations[] = new ManifestViolation($field, sprintf('"%s" is listed twice', $action));

                continue;
            }

            $seen[$action] = true;
            $valid[]       = $action;
        }

        return $valid;
    }

    /**
     * The console UI description is a file inside the package: see {@see PackagePath::isRelativeFile()}.
     *
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    private function checkUi(array $block, array &$violations): ?string
    {
        if (!array_key_exists('ui', $block)) {
            return null;
        }

        $ui = $block['ui'];

        if (!PackagePath::isRelativeFile($ui, ManifestSchema::UI_EXTENSION)) {
            $violations[] = new ManifestViolation(
                'extra.fapost.ui',
                sprintf('must be a path relative to the package root of segments made of letters, digits, ".", "_" and "-", no segment starting with ".", ending with "%s"', ManifestSchema::UI_EXTENSION),
            );

            return null;
        }

        /** @var string $ui */
        return $ui;
    }
}
