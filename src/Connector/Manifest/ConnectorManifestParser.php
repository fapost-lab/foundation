<?php

declare(strict_types=1);

namespace Fapost\Foundation\Connector\Manifest;

use Fapost\Foundation\Manifest\Internal\ManifestRules;
use Fapost\Foundation\Solution\Manifest\ManifestSchema;
use Fapost\Foundation\Solution\Manifest\ManifestViolation;

/**
 * Checks a decoded `composer.json` against the connector manifest schema.
 *
 * Pure: it reads an array, touches no file, loads no class and runs no package code, so a
 * connector's CI can run it without Core. It collects every violation instead of stopping at the
 * first, so an author fixes a manifest in one pass. What needs the installation (the provider
 * class exists, ids and channel types are unique across packages) is checked by the caller.
 *
 * Since foundation 0.21, experimental.
 */
final class ConnectorManifestParser
{
    private readonly ManifestRules $rules;

    public function __construct()
    {
        $this->rules = new ManifestRules('connector', ConnectorManifestSchema::SUPPORTED);
    }

    /**
     * @param  array<string, mixed>  $composer  decoded composer.json of the package
     */
    public function parse(array $composer): ConnectorManifestParseResult
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
                return new ConnectorManifestParseResult($package, null, $violations);
            }
        }

        if (!is_string($composer['name'] ?? null) || '' === $composer['name']) {
            $violations[] = new ManifestViolation('name', 'required');
        }

        if (ManifestSchema::CONNECTOR_PACKAGE_TYPE !== ($composer['type'] ?? null)) {
            $violations[] = new ManifestViolation('type', sprintf('must be "%s"', ManifestSchema::CONNECTOR_PACKAGE_TYPE));
        }

        $violations = [...$violations, ...$this->rules->checkRequire($composer), ...$this->rules->checkLaravelProviders($composer)];

        if (!is_array($block) || array_is_list($block) && [] !== $block) {
            $violations[] = new ManifestViolation('extra.fapost', 'required: an object with the connector manifest');

            return new ConnectorManifestParseResult($package, null, $violations);
        }

        /** @var array<string, mixed> $block */
        $schema = $block['schema'];

        $id          = $this->rules->checkId($block, $violations);
        $name        = $this->rules->checkName($block, $violations);
        $description = $this->rules->checkDescription($block, $violations);
        $provider    = $this->rules->checkProvider($block, $violations);
        $channels    = $this->checkChannels($block, $violations);

        foreach (array_keys($block) as $key) {
            if (!in_array($key, ConnectorManifestSchema::FIELDS, true) && !str_starts_with((string) $key, ManifestSchema::EXTENSION_PREFIX)) {
                $violations[] = new ManifestViolation(
                    'extra.fapost.' . $key,
                    sprintf(
                        'unknown field (schema %d allows: %s); a newer fapost/foundation may be required',
                        $schema,
                        implode(', ', ConnectorManifestSchema::FIELDS),
                    ),
                );
            }
        }

        $constraint = $this->rules->foundationConstraint($composer);

        if ([] !== $violations || null === $id || null === $name || null === $provider || null === $constraint) {
            return new ConnectorManifestParseResult($package, null, $violations);
        }

        return new ConnectorManifestParseResult(
            $package,
            new ConnectorManifest($package, $schema, $id, $name, $description, $provider, $channels, $constraint),
            [],
        );
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<ManifestViolation>  $violations
     * @return list<string>
     */
    private function checkChannels(array $block, array &$violations): array
    {
        if (!array_key_exists('channels', $block) || null === $block['channels']) {
            $violations[] = new ManifestViolation('extra.fapost.channels', 'required');

            return [];
        }

        $channels = $block['channels'];

        if (!is_array($channels) || !array_is_list($channels) || [] === $channels) {
            $violations[] = new ManifestViolation('extra.fapost.channels', 'must be a non-empty list of channel types');

            return [];
        }

        $valid = [];
        $seen  = [];

        foreach ($channels as $index => $channel) {
            $field = sprintf('extra.fapost.channels[%d]', $index);

            if (!is_string($channel) || 1 !== preg_match(ConnectorManifestSchema::CHANNEL_TYPE_PATTERN, $channel)) {
                $violations[] = new ManifestViolation($field, 'must be a string matching ' . ConnectorManifestSchema::channelTypePattern());

                continue;
            }

            if (isset($seen[$channel])) {
                $violations[] = new ManifestViolation($field, sprintf('"%s" is listed twice', $channel));

                continue;
            }

            $seen[$channel] = true;
            $valid[]        = $channel;
        }

        return $valid;
    }
}
