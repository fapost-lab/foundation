<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

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
    private const string CLASS_NAME_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/D';

    private const string ACTION_TAIL = '[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*';

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
            $schema = $this->checkSchema($block, $violations);

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

        $violations = [...$violations, ...$this->checkRequire($composer), ...$this->checkLaravelProviders($composer)];

        if (!is_array($block) || array_is_list($block) && [] !== $block) {
            $violations[] = new ManifestViolation('extra.fapost', 'required: an object with the Solution manifest');

            return new ManifestParseResult($package, null, $violations);
        }

        /** @var array<string, mixed> $block */
        $schema = $block['schema'];

        $id          = $this->checkId($block, $violations);
        $name        = $this->checkName($block, $violations);
        $description = $this->checkDescription($block, $violations);
        $provider    = $this->checkProvider($block, $violations);
        $actions     = $this->checkActions($block, $id, $violations);

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

        $constraint = $this->foundationConstraint($composer);

        if ([] !== $violations || null === $id || null === $name || null === $provider || null === $constraint) {
            return new ManifestParseResult($package, null, $violations);
        }

        return new ManifestParseResult(
            $package,
            new SolutionManifest($package, $schema, $id, $name, $description, $provider, $actions, $constraint),
            [],
        );
    }

    /**
     * @param  array<string, mixed>  $composer
     * @return list<ManifestViolation>
     */
    private function checkRequire(array $composer): array
    {
        $require    = $composer['require'] ?? [];
        $require    = is_array($require) ? $require : [];
        $violations = [];

        if (null === $this->foundationConstraint($composer)) {
            $violations[] = new ManifestViolation('require.' . ManifestSchema::FOUNDATION_PACKAGE, 'a Solution must require ' . ManifestSchema::FOUNDATION_PACKAGE);
        }

        if (array_key_exists(ManifestSchema::FORBIDDEN_PACKAGE, $require)) {
            $violations[] = new ManifestViolation(
                'require.' . ManifestSchema::FORBIDDEN_PACKAGE,
                'a Solution depends on ' . ManifestSchema::FOUNDATION_PACKAGE . ', never on Core',
            );
        }

        return $violations;
    }

    /**
     * @param  array<string, mixed>  $composer
     */
    private function foundationConstraint(array $composer): ?string
    {
        $require    = $composer['require'] ?? null;
        $constraint = is_array($require) ? ($require[ManifestSchema::FOUNDATION_PACKAGE] ?? null) : null;

        return is_string($constraint) && '' !== mb_trim($constraint) ? $constraint : null;
    }

    /**
     * Core boots a Solution's provider itself, so Laravel's package discovery must have none to boot:
     * any entry, in any spelling, is refused; an empty list is harmless.
     *
     * @param  array<string, mixed>  $composer
     * @return list<ManifestViolation>
     */
    private function checkLaravelProviders(array $composer): array
    {
        $extra   = $composer['extra'] ?? null;
        $laravel = is_array($extra) ? ($extra['laravel'] ?? null) : null;
        $listed  = is_array($laravel) ? ($laravel['providers'] ?? []) : [];

        if ([] === $listed || null === $listed) {
            return [];
        }

        return [new ManifestViolation(
            'extra.laravel.providers',
            "a Solution's provider is booted by Core; remove this block",
        )];
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    private function checkSchema(array $block, array &$violations): ?int
    {
        $schema = $block['schema'] ?? null;

        if (null === $schema) {
            $violations[] = new ManifestViolation('extra.fapost.schema', 'required');

            return null;
        }

        if (!is_int($schema)) {
            $violations[] = new ManifestViolation('extra.fapost.schema', 'must be an integer');

            return null;
        }

        if (!in_array($schema, ManifestSchema::SUPPORTED, true)) {
            $violations[] = new ManifestViolation(
                'extra.fapost.schema',
                sprintf('%d is not supported by this fapost/foundation (supports: %s)', $schema, implode(', ', ManifestSchema::SUPPORTED)),
            );

            return null;
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    private function checkId(array $block, array &$violations): ?string
    {
        $id = $block['id'] ?? null;

        if (null === $id) {
            $violations[] = new ManifestViolation('extra.fapost.id', 'required');

            return null;
        }

        if (!is_string($id) || 1 !== preg_match(ManifestSchema::ID_PATTERN, $id)) {
            $violations[] = new ManifestViolation('extra.fapost.id', 'must match ' . ManifestSchema::idPattern());

            return null;
        }

        if (in_array($id, ManifestSchema::RESERVED_IDS, true)) {
            $violations[] = new ManifestViolation(
                'extra.fapost.id',
                sprintf('"%s" is reserved (reserved: %s)', $id, implode(', ', ManifestSchema::RESERVED_IDS)),
            );

            return null;
        }

        return $id;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    private function checkName(array $block, array &$violations): ?string
    {
        $name = $block['name'] ?? null;

        if (null === $name) {
            $violations[] = new ManifestViolation('extra.fapost.name', 'required');

            return null;
        }

        if (!is_string($name) || '' === mb_trim($name) || mb_strlen($name) > ManifestSchema::NAME_MAX_LENGTH || 1 === preg_match('/[\r\n]/', $name)) {
            $violations[] = new ManifestViolation(
                'extra.fapost.name',
                sprintf('must be a single line of 1 to %d characters', ManifestSchema::NAME_MAX_LENGTH),
            );

            return null;
        }

        return $name;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    private function checkDescription(array $block, array &$violations): ?string
    {
        if (!array_key_exists('description', $block)) {
            return null;
        }

        $description = $block['description'];

        if (!is_string($description) || mb_strlen($description) > ManifestSchema::DESCRIPTION_MAX_LENGTH) {
            $violations[] = new ManifestViolation(
                'extra.fapost.description',
                sprintf('must be a string of up to %d characters', ManifestSchema::DESCRIPTION_MAX_LENGTH),
            );

            return null;
        }

        return $description;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    private function checkProvider(array $block, array &$violations): ?string
    {
        $provider = $block['provider'] ?? null;

        if (null === $provider) {
            $violations[] = new ManifestViolation('extra.fapost.provider', 'required');

            return null;
        }

        if (!is_string($provider) || 1 !== preg_match(self::CLASS_NAME_PATTERN, $provider)) {
            $violations[] = new ManifestViolation('extra.fapost.provider', 'must be a fully qualified class name without a leading backslash');

            return null;
        }

        return $provider;
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
}
