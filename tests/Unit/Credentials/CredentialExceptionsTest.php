<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Credentials;

use Fapost\Foundation\Credentials\Exceptions\CredentialNotFoundException;
use Fapost\Foundation\Credentials\Exceptions\CredentialUnavailableException;
use Fapost\Foundation\Flow\Call\CallRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CredentialExceptionsTest extends TestCase
{
    public function test_not_found_names_the_credential(): void
    {
        $exception = CredentialNotFoundException::forId('cred-1');

        $this->assertSame('cred-1', $exception->credentialId);
        $this->assertSame('Credential "cred-1" was not found.', $exception->getMessage());
    }

    public function test_unavailable_names_the_credential_and_the_reason(): void
    {
        $previous  = new RuntimeException('connection refused');
        $exception = CredentialUnavailableException::because('cred-1', 'the token endpoint answered 500.', $previous);

        $this->assertSame('cred-1', $exception->credentialId);
        $this->assertSame('Credential "cred-1" is unavailable: the token endpoint answered 500.', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_a_call_request_without_a_credential_is_built_and_serialized_as_before(): void
    {
        $request = new CallRequest('POST https://api.example.com', ['a' => 1], ['timeout' => 5]);

        $this->assertNull($request->credential);
        $this->assertEquals($request, unserialize(serialize($request)));
    }
}
