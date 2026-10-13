<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Manifest;

/**
 * What a Solution manifest may contain, as data.
 *
 * The manifest is the `extra.fapost` block of the package's own `composer.json`; the package
 * `type` is {@see self::PACKAGE_TYPE}. The parser, the documentation and any tooling read the
 * same constants, so there is one place where the format is defined.
 *
 * Evolution policy:
 * - a new optional field is additive: same schema, a minor Foundation release, documented with
 *   "since foundation 0.x"; a Solution that uses it requires that Foundation version in `require`;
 * - a change of meaning, a removal or a new required field is a new schema number; Foundation
 *   reads schema N and N-1 within one major version (N-1 deprecated), and refuses anything else;
 * - unknown fields are an error, except the ones starting with {@see self::EXTENSION_PREFIX}.
 */
final class ManifestSchema
{
    /** `type` of a Solution package in its composer.json. */
    public const string PACKAGE_TYPE = 'fapost-solution';

    /** `type` reserved for Plugin packages; Plugins are not discovered yet. */
    public const string PLUGIN_PACKAGE_TYPE = 'fapost-plugin';

    /** The schema number this Foundation writes and reads as current. */
    public const int CURRENT = 1;

    /**
     * Schema numbers this Foundation reads: the current one and, within one major version, the previous one.
     *
     * @var list<int>
     */
    public const array SUPPORTED = [1];

    public const string ID_PATTERN = '/^[a-z][a-z0-9_]{1,31}$/D';

    /**
     * Ids no Solution may take: they name the platform itself or a built-in area.
     *
     * @var list<string>
     */
    public const array RESERVED_IDS = ['core', 'platform', 'fapost', 'app', 'system', 'flow', 'module', 'rag'];

    public const int NAME_MAX_LENGTH = 60;

    public const int DESCRIPTION_MAX_LENGTH = 280;

    /** Keys starting with this prefix are left to the author's own tooling and ignored. */
    public const string EXTENSION_PREFIX = 'x-';

    /** The one package every Solution must require; Composer enforces the constraint at install time. */
    public const string FOUNDATION_PACKAGE = 'fapost/foundation';

    /** A Solution depends on contracts, never on Core. */
    public const string FORBIDDEN_PACKAGE = 'fapost/core';

    /**
     * Fields of `extra.fapost` in schema 1.
     *
     * @var list<string>
     */
    public const array FIELDS = ['schema', 'id', 'name', 'description', 'provider', 'actions'];

    /**
     * Machine-readable description of schema 1, keyed by field path.
     *
     * @return array<string, array{required: bool, rule: string}>
     */
    public static function describe(): array
    {
        return [
            'type'                                         => ['required' => true, 'rule' => 'must be "' . self::PACKAGE_TYPE . '"'],
            'extra.fapost.schema'                          => ['required' => true, 'rule' => 'integer; supported: ' . implode(', ', self::SUPPORTED)],
            'extra.fapost.id'                              => ['required' => true, 'rule' => 'must match ' . self::idPattern() . '; not one of: ' . implode(', ', self::RESERVED_IDS) . '; never changes'],
            'extra.fapost.name'                            => ['required' => true, 'rule' => '1 to ' . self::NAME_MAX_LENGTH . ' characters, one line'],
            'extra.fapost.description'                     => ['required' => false, 'rule' => 'up to ' . self::DESCRIPTION_MAX_LENGTH . ' characters'],
            'extra.fapost.provider'                        => ['required' => true, 'rule' => 'class name of a service provider extending Fapost\Foundation\Lifecycle\AbstractSolutionServiceProvider'],
            'extra.fapost.actions'                         => ['required' => false, 'rule' => 'list of unique action ids, each prefixed "<id>."; default []'],
            'extra.fapost.' . self::EXTENSION_PREFIX . '*' => ['required' => false, 'rule' => 'ignored; for the author\'s own tooling'],
            'extra.laravel.providers'                      => ['required' => false, 'rule' => 'must be absent or empty: Core boots the provider'],
            'require.' . self::FOUNDATION_PACKAGE          => ['required' => true, 'rule' => 'a constraint; checked by Composer'],
            'require.' . self::FORBIDDEN_PACKAGE           => ['required' => false, 'rule' => 'forbidden'],
        ];
    }

    public static function idPattern(): string
    {
        return '^[a-z][a-z0-9_]{1,31}$';
    }
}
