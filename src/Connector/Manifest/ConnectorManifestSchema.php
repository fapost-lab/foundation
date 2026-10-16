<?php

declare(strict_types=1);

namespace Fapost\Foundation\Connector\Manifest;

use Fapost\Foundation\Lifecycle\AbstractConnectorServiceProvider;
use Fapost\Foundation\Solution\Manifest\ManifestSchema;

/**
 * What a connector manifest may contain, as data.
 *
 * The manifest is the `extra.fapost` block of the package's own `composer.json`; the package
 * `type` is {@see ManifestSchema::CONNECTOR_PACKAGE_TYPE}. The parser, the documentation and any
 * tooling read the same constants, so there is one place where the format is defined. The id,
 * name and description limits, the reserved ids and the rules for `require` are the Solution
 * ones, read from {@see ManifestSchema}.
 *
 * Evolution policy:
 * - a new optional field is additive: same schema, a minor Foundation release, documented with
 *   "since foundation 0.x"; a connector that uses it requires that Foundation version in `require`;
 * - a change of meaning, a removal or a new required field is a new schema number; Foundation
 *   reads schema N and N-1 within one major version (N-1 deprecated), and refuses anything else;
 * - unknown fields are an error, except the ones starting with {@see ManifestSchema::EXTENSION_PREFIX}.
 *
 * Since foundation 0.21, experimental.
 */
final class ConnectorManifestSchema
{
    /** The schema number this Foundation writes and reads as current. */
    public const int CURRENT = 1;

    /**
     * Schema numbers this Foundation reads: the current one and, within one major version, the previous one.
     *
     * @var list<int>
     */
    public const array SUPPORTED = [1];

    public const string CHANNEL_TYPE_PATTERN = '/^[a-z][a-z0-9_]{1,31}$/D';

    /**
     * Fields of `extra.fapost` in schema 1 for a connector.
     *
     * @var list<string>
     */
    public const array FIELDS = ['schema', 'id', 'name', 'description', 'provider', 'channels'];

    /**
     * Machine-readable description of schema 1, keyed by field path.
     *
     * @return array<string, array{required: bool, rule: string}>
     */
    public static function describe(): array
    {
        return [
            'type'                                                   => ['required' => true, 'rule' => 'must be "' . ManifestSchema::CONNECTOR_PACKAGE_TYPE . '"'],
            'extra.fapost.schema'                                    => ['required' => true, 'rule' => 'integer; supported: ' . implode(', ', self::SUPPORTED)],
            'extra.fapost.id'                                        => ['required' => true, 'rule' => 'must match ' . ManifestSchema::idPattern() . '; not one of: ' . implode(', ', ManifestSchema::RESERVED_IDS) . '; never changes'],
            'extra.fapost.name'                                      => ['required' => true, 'rule' => '1 to ' . ManifestSchema::NAME_MAX_LENGTH . ' characters, one line'],
            'extra.fapost.description'                               => ['required' => false, 'rule' => 'up to ' . ManifestSchema::DESCRIPTION_MAX_LENGTH . ' characters'],
            'extra.fapost.provider'                                  => ['required' => true, 'rule' => 'class name of a service provider extending ' . AbstractConnectorServiceProvider::class],
            'extra.fapost.channels'                                  => ['required' => true, 'rule' => 'non-empty list of unique channel types, each matching ' . self::channelTypePattern() . '; the connector owns them'],
            'extra.fapost.' . ManifestSchema::EXTENSION_PREFIX . '*' => ['required' => false, 'rule' => 'ignored; for the author\'s own tooling'],
            'extra.laravel.providers'                                => ['required' => false, 'rule' => 'must be absent or empty: Core boots the provider'],
            'require.' . ManifestSchema::FOUNDATION_PACKAGE          => ['required' => true, 'rule' => 'a constraint; checked by Composer'],
            'require.' . ManifestSchema::FORBIDDEN_PACKAGE           => ['required' => false, 'rule' => 'forbidden'],
        ];
    }

    public static function channelTypePattern(): string
    {
        return '^[a-z][a-z0-9_]{1,31}$';
    }
}
