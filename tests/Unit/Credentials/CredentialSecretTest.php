<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Credentials;

use Fapost\Foundation\Credentials\DTO\CredentialSecret;
use Fapost\Foundation\Flow\Call\CallRequest;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use SensitiveParameter;

final class CredentialSecretTest extends TestCase
{
    private const string SECRET = 's3cr3t-token-value';

    /**
     * @return iterable<string, array{0: array<mixed>, 1: array<mixed>, 2: array<mixed>}>
     */
    public static function invalidMembers(): iterable
    {
        yield 'a non-string setting' => [['header' => 1], [], ['token' => 'x']];
        yield 'a list of settings' => [['X-Api-Key'], [], ['token' => 'x']];
        yield 'an empty host' => [[], [''], ['token' => 'x']];
        yield 'a keyed host list' => [[], ['a' => 'api.example.com'], ['token' => 'x']];
        yield 'a non-string value' => [[], [], ['token' => 12345678]];
    }

    public function test_it_exposes_its_values_and_settings_by_key(): void
    {
        $secret = $this->secret();

        $this->assertSame(self::SECRET, $secret->value('token'));
        $this->assertNull($secret->value('missing'));
        $this->assertSame('X-Api-Key', $secret->setting('header'));
        $this->assertNull($secret->setting('missing'));
        $this->assertSame(['api.example.com', '*.example.org'], $secret->allowedHosts);
    }

    public function test_var_dump_and_print_r_mask_the_values(): void
    {
        $secret = $this->secret();

        ob_start();
        var_dump($secret);
        $dumped = (string) ob_get_clean();

        $printed = print_r($secret, true);

        foreach ([$dumped, $printed] as $output) {
            $this->assertStringNotContainsString(self::SECRET, $output);
            $this->assertStringContainsString('***', $output);
            $this->assertStringContainsString('cred-1', $output);
        }
    }

    public function test_json_encode_leaves_the_values_out(): void
    {
        $encoded = (string) json_encode($this->secret());

        $this->assertStringNotContainsString(self::SECRET, $encoded);
        $this->assertStringNotContainsString('values', $encoded);
        $this->assertStringContainsString('cred-1', $encoded);
    }

    public function test_it_cannot_be_serialized(): void
    {
        $this->expectException(LogicException::class);

        serialize($this->secret());
    }

    public function test_a_call_request_holding_it_cannot_be_serialized(): void
    {
        $this->expectException(LogicException::class);

        serialize(new CallRequest('GET https://api.example.com', credential: $this->secret()));
    }

    public function test_it_cannot_be_unserialized_from_a_crafted_payload(): void
    {
        $payload = sprintf('O:%d:"%s":0:{}', mb_strlen(CredentialSecret::class), CredentialSecret::class);

        $this->expectException(LogicException::class);

        unserialize($payload);
    }

    public function test_the_values_parameter_is_kept_out_of_stack_traces(): void
    {
        $parameter = new ReflectionParameter([CredentialSecret::class, '__construct'], 'values');

        $this->assertCount(1, $parameter->getAttributes(SensitiveParameter::class));
    }

    /**
     * @param  array<mixed>  $settings
     * @param  array<mixed>  $hosts
     * @param  array<mixed>  $values
     */
    #[DataProvider('invalidMembers')]
    public function test_it_refuses_members_of_the_wrong_shape(array $settings, array $hosts, array $values): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore argument.type */
        new CredentialSecret('tenant-1', 'cred-1', 'bearer', 'v1', $settings, $hosts, $values);
    }

    public function test_the_refusal_of_a_value_does_not_carry_it(): void
    {
        try {
            /** @phpstan-ignore argument.type */
            new CredentialSecret('tenant-1', 'cred-1', 'bearer', 'v1', [], [], ['token' => ['nested' => self::SECRET]]);
            $this->fail('A non-string value was accepted.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringNotContainsString(self::SECRET, $exception->getMessage());
        }
    }

    public function test_it_needs_its_identifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CredentialSecret('tenant-1', '', 'bearer', 'v1', [], [], []);
    }

    private function secret(): CredentialSecret
    {
        return new CredentialSecret(
            tenantId: 'tenant-1',
            id: 'cred-1',
            type: 'api_key_header',
            version: '2026-11-19T10:00:00.000000Z',
            settings: ['header' => 'X-Api-Key'],
            allowedHosts: ['api.example.com', '*.example.org'],
            values: ['token' => self::SECRET],
        );
    }
}
