<?php

declare(strict_types=1);

namespace Fapost\Foundation\Credentials\Contracts;

use Fapost\Foundation\Credentials\DTO\CredentialSecret;
use Fapost\Foundation\Credentials\DTO\HttpAuthorization;
use Fapost\Foundation\Credentials\Exceptions\CredentialUnavailableException;

/**
 * A credential type that authorizes an outgoing HTTP request.
 *
 * The caller checks the target host against {@see CredentialSecret::$allowedHosts} before it asks, applies what
 * it gets back, and replaces each of {@see HttpAuthorization::secrets()} in the response.
 *
 * @experimental
 */
interface HttpCredentialTypeInterface extends CredentialTypeInterface
{
    /**
     * @throws CredentialUnavailableException when the credential cannot be applied now
     */
    public function authorize(CredentialSecret $secret): HttpAuthorization;
}
