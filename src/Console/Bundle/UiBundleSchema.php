<?php

declare(strict_types=1);

namespace Fapost\Foundation\Console\Bundle;

/**
 * What a Solution's `fapost-ui.json` may contain, as data.
 *
 * The file sits next to the built console UI of a Solution (its `dist/`) and is referenced from the manifest's
 * `extra.fapost.ui`. It lists the pages and components the Solution ships and the versions of the console kit,
 * Vue and Inertia it was built against. Evolution follows the manifest's policy
 * ({@see \Fapost\Foundation\Solution\Manifest\ManifestSchema}): additive fields in a minor release, a new
 * `schema` number for anything else; unknown fields other than `x-*` are an error.
 *
 * @experimental
 */
final class UiBundleSchema
{
    /** The schema number this Foundation writes and reads as current. */
    public const int CURRENT = 1;

    /**
     * Schema numbers this Foundation reads.
     *
     * @var list<int>
     */
    public const array SUPPORTED = [1];

    /**
     * Fields of `fapost-ui.json` in schema 1.
     *
     * @var list<string>
     */
    public const array FIELDS = ['schema', 'solution', 'kit', 'vue', 'inertia', 'pages', 'components', 'css'];

    /** Keys starting with this prefix are left to the author's own tooling and ignored. */
    public const string EXTENSION_PREFIX = 'x-';

    /** Extension of a page or component file. */
    public const string SCRIPT_EXTENSION = '.js';

    /** Extension of a stylesheet file. */
    public const string STYLE_EXTENSION = '.css';

    /**
     * Machine-readable description of schema 1, keyed by field.
     *
     * @return array<string, array{required: bool, rule: string}>
     *
     * @experimental
     */
    public static function describe(): array
    {
        $path = '^[A-Z][A-Za-z0-9]*(/[A-Z][A-Za-z0-9]*)*$';
        $file = 'a path relative to the directory of fapost-ui.json, without ".." segments';

        return [
            'schema'                     => ['required' => true, 'rule' => 'integer; supported: ' . implode(', ', self::SUPPORTED)],
            'solution'                   => ['required' => true, 'rule' => 'the id of the Solution whose manifest references this file'],
            'kit'                        => ['required' => true, 'rule' => 'non-empty version constraint of the console kit the bundle was built against; Core checks it'],
            'vue'                        => ['required' => true, 'rule' => 'non-empty version constraint of Vue; Core checks it'],
            'inertia'                    => ['required' => true, 'rule' => 'non-empty version constraint of Inertia; Core checks it'],
            'pages'                      => ['required' => false, 'rule' => 'object: Path matching ' . $path . ' => ' . $file . ', ending with "' . self::SCRIPT_EXTENSION . '"; default {}'],
            'components'                 => ['required' => false, 'rule' => 'object: Path matching ' . $path . ' => ' . $file . ', ending with "' . self::SCRIPT_EXTENSION . '"; default {}; pages and components together hold at least one entry'],
            'css'                        => ['required' => false, 'rule' => 'list of ' . $file . ', each ending with "' . self::STYLE_EXTENSION . '"; default []'],
            self::EXTENSION_PREFIX . '*' => ['required' => false, 'rule' => 'ignored; for the author\'s own tooling'],
        ];
    }
}
