<?php

declare(strict_types=1);

namespace Fapost\Foundation\Manifest\Internal;

use Fapost\Foundation\Solution\Manifest\ManifestSchema;
use Fapost\Foundation\Solution\Manifest\ManifestViolation;

/**
 * The checks a Solution manifest and a connector manifest share: schema number, id, name,
 * description, provider, the `require` block and Laravel's package discovery.
 *
 * Both parsers use it so the two formats cannot drift apart. The id, name and description
 * limits are the ones in {@see ManifestSchema}. The subject ("Solution", "connector") is the
 * word the messages use for the package being checked.
 *
 * @internal not part of the public API; may change in any release
 */
final readonly class ManifestRules
{
    private const string CLASS_NAME_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/D';

    /**
     * @param  string  $subject  what the package is called in messages, such as "Solution" or "connector"
     * @param  list<int>  $supportedSchemas  schema numbers the manifest format reads
     */
    public function __construct(
        private string $subject,
        private array $supportedSchemas,
    ) {
    }

    /**
     * @param  array<string, mixed>  $composer
     * @return list<ManifestViolation>
     */
    public function checkRequire(array $composer): array
    {
        $require    = $composer['require'] ?? [];
        $require    = is_array($require) ? $require : [];
        $violations = [];

        if (null === $this->foundationConstraint($composer)) {
            $violations[] = new ManifestViolation('require.' . ManifestSchema::FOUNDATION_PACKAGE, sprintf('a %s must require ', $this->subject) . ManifestSchema::FOUNDATION_PACKAGE);
        }

        if (array_key_exists(ManifestSchema::FORBIDDEN_PACKAGE, $require)) {
            $violations[] = new ManifestViolation(
                'require.' . ManifestSchema::FORBIDDEN_PACKAGE,
                sprintf('a %s depends on ', $this->subject) . ManifestSchema::FOUNDATION_PACKAGE . ', never on Core',
            );
        }

        return $violations;
    }

    /**
     * @param  array<string, mixed>  $composer
     */
    public function foundationConstraint(array $composer): ?string
    {
        $require    = $composer['require'] ?? null;
        $constraint = is_array($require) ? ($require[ManifestSchema::FOUNDATION_PACKAGE] ?? null) : null;

        return is_string($constraint) && '' !== mb_trim($constraint) ? $constraint : null;
    }

    /**
     * Core boots the provider itself, so Laravel's package discovery must have none to boot:
     * any entry, in any spelling, is refused; an empty list is harmless.
     *
     * @param  array<string, mixed>  $composer
     * @return list<ManifestViolation>
     */
    public function checkLaravelProviders(array $composer): array
    {
        $extra   = $composer['extra'] ?? null;
        $laravel = is_array($extra) ? ($extra['laravel'] ?? null) : null;
        $listed  = is_array($laravel) ? ($laravel['providers'] ?? []) : [];

        if ([] === $listed || null === $listed) {
            return [];
        }

        return [new ManifestViolation(
            'extra.laravel.providers',
            sprintf("a %s's provider is booted by Core; remove this block", $this->subject),
        )];
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    public function checkSchema(array $block, array &$violations): ?int
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

        if (!in_array($schema, $this->supportedSchemas, true)) {
            $violations[] = new ManifestViolation(
                'extra.fapost.schema',
                sprintf('%d is not supported by this fapost/foundation (supports: %s)', $schema, implode(', ', $this->supportedSchemas)),
            );

            return null;
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     */
    public function checkId(array $block, array &$violations): ?string
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
    public function checkName(array $block, array &$violations): ?string
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
    public function checkDescription(array $block, array &$violations): ?string
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
    public function checkProvider(array $block, array &$violations): ?string
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
}
