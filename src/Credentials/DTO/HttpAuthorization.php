<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\DTO;

use InvalidArgumentException;
use LogicException;
use SensitiveParameter;

/**
 * What an HTTP credential type adds to a request, returned by
 * {@see \Fapost\Foundation\Credentials\Contracts\HttpCredentialTypeInterface::authorize()}.
 *
 * - `headers()` — header name => value, applied over the request's own headers of the same name;
 * - `query()` — query parameter => value, applied over the request's own parameters of the same name;
 * - `secrets()` — every string that must not appear in what the call returns: each secret value and
 *   anything derived from it (`base64(user:pass)`, an OAuth2 access token, a URL-encoded key). The caller
 *   replaces each of them in the result before anyone sees it.
 *
 * It carries the secrets it applies, so it is guarded like {@see CredentialSecret}: its members are private,
 * `var_dump()` and `print_r()` show `***` for each value, the constructor's parameters are
 * `#[SensitiveParameter]`, and `serialize()` throws. `var_export()`, an `(array)` cast and Symfony's VarDumper
 * (`dump()`, `dd()`) still read it.
 *
 * @experimental
 */
final readonly class HttpAuthorization
{
    private const string MASK = '***';

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, string>  $query
     * @param  list<string>  $secrets
     *
     * @throws InvalidArgumentException when a name is empty or a value is not a string; the message never
     *                                  carries a value
     */
    public function __construct(
        #[SensitiveParameter]
        private array $headers = [],
        #[SensitiveParameter]
        private array $query = [],
        #[SensitiveParameter]
        private array $secrets = [],
    ) {
        foreach (['header' => $headers, 'query parameter' => $query] as $what => $map) {
            foreach ($map as $name => $value) {
                if (!is_string($name) || '' === $name || !is_string($value)) {
                    throw new InvalidArgumentException(sprintf('An HTTP authorization %s needs a name and a string value.', $what));
                }
            }
        }

        if ($secrets !== array_values(array_filter($secrets, is_string(...)))) {
            throw new InvalidArgumentException('An HTTP authorization must list its secrets as strings.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        $mask = static fn (): string => self::MASK;

        return [
            'headers' => array_map($mask, $this->headers),
            'query'   => array_map($mask, $this->query),
            'secrets' => array_map($mask, $this->secrets),
        ];
    }

    /**
     * @throws LogicException always: an authorization is never serialized
     */
    public function __serialize(): array
    {
        throw new LogicException('An HTTP authorization holds secret values and cannot be serialized.');
    }

    /**
     * @param  array<mixed>  $data
     *
     * @throws LogicException always: an authorization is never unserialized
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('An HTTP authorization cannot be unserialized.');
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * @return array<string, string>
     */
    public function query(): array
    {
        return $this->query;
    }

    /**
     * @return list<string>
     */
    public function secrets(): array
    {
        return $this->secrets;
    }
}
