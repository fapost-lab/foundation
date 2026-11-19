<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\DTO;

use InvalidArgumentException;
use LogicException;
use SensitiveParameter;

/**
 * A credential with its decrypted secret values, for the moment it is applied to a request. Returned by
 * {@see \Fapost\Foundation\Credentials\Contracts\CredentialStoreInterface::secret()}.
 *
 * It lives in memory for one call and is never stored again:
 *
 * - the secret values are private and read with {@see value()}, so `json_encode()` and a log context that
 *   encodes the object see only the public, non-secret members;
 * - `var_dump()` and `print_r()` show each secret value as `***` ({@see __debugInfo()});
 * - the constructor's `$values` is a `#[SensitiveParameter]`, so a stack trace does not carry it;
 * - `serialize()` throws, so the object cannot reach a queue, a cache or a session — and neither can an
 *   object that holds it, such as a {@see \Fapost\Foundation\Flow\Call\CallRequest}.
 *
 * `var_export()`, an `(array)` cast and Symfony's VarDumper (`dump()`, `dd()`, a dump in Telescope) still read
 * private properties: VarDumper shows the masked copy next to the real one. PHP offers no hook to stop them;
 * do not pass this object to any of them.
 *
 * Settings are the credential's non-secret fields; `allowedHosts` names the hosts the credential may be sent
 * to — an exact host name or `*.example.com` for its subdomains. An empty list allows no host.
 *
 * @experimental
 */
final readonly class CredentialSecret
{
    private const string MASK = '***';

    /**
     * @param  string  $tenantId  the tenant the credential belongs to
     * @param  string  $id  the credential's id
     * @param  string  $type  the id of its credential type, such as "bearer"
     * @param  string  $version  opaque; changes every time the credential is saved, so a cache keyed by it
     *                           (an OAuth2 token, for one) stops matching when a secret is replaced
     * @param  array<string, string>  $settings  non-secret field key => value
     * @param  list<string>  $allowedHosts  hosts the credential may be sent to
     * @param  array<string, string>  $values  secret field key => decrypted value
     *
     * @throws InvalidArgumentException when an identifier is empty or a setting, host or value is not a string;
     *                                  the message never carries a value
     */
    public function __construct(
        public string $tenantId,
        public string $id,
        public string $type,
        public string $version,
        public array $settings,
        public array $allowedHosts,
        #[SensitiveParameter]
        private array $values,
    ) {
        if ('' === $tenantId || '' === $id || '' === $type || '' === $version) {
            throw new InvalidArgumentException('A credential secret needs a tenant id, an id, a type and a version.');
        }

        self::assertStringMap($settings, sprintf('Credential "%s" has a setting that is not a string.', $id));
        self::assertStringMap($values, sprintf('Credential "%s" has a secret value that is not a string.', $id));

        if ($allowedHosts !== array_values(array_filter($allowedHosts, static fn (mixed $host): bool => is_string($host) && '' !== $host))) {
            throw new InvalidArgumentException(sprintf('Credential "%s" must list its allowed hosts as non-empty strings.', $id));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'tenantId'     => $this->tenantId,
            'id'           => $this->id,
            'type'         => $this->type,
            'version'      => $this->version,
            'settings'     => $this->settings,
            'allowedHosts' => $this->allowedHosts,
            'values'       => array_map(static fn (): string => self::MASK, $this->values),
        ];
    }

    /**
     * @throws LogicException always: a secret is never serialized
     */
    public function __serialize(): array
    {
        throw new LogicException(sprintf('Credential "%s" holds secret values and cannot be serialized.', $this->id));
    }

    /**
     * @param  array<mixed>  $data
     *
     * @throws LogicException always: a secret is never unserialized
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('A credential secret cannot be unserialized.');
    }

    /**
     * The decrypted value of a secret field; null when the credential has none for the key.
     */
    public function value(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    /**
     * The value of a non-secret field; null when the credential has none for the key.
     */
    public function setting(string $key): ?string
    {
        return $this->settings[$key] ?? null;
    }

    /**
     * @param  array<mixed>  $map
     */
    private static function assertStringMap(array $map, string $message): void
    {
        foreach ($map as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                throw new InvalidArgumentException($message);
            }
        }
    }
}
