<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Credentials;

use Fapost\Foundation\Credentials\DTO\HttpAuthorization;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SensitiveParameter;

final class HttpAuthorizationTest extends TestCase
{
    private const string TOKEN = 'access-token-1234';

    public function test_it_returns_what_it_applies(): void
    {
        $authorization = $this->authorization();

        $this->assertSame(['Authorization' => 'Bearer ' . self::TOKEN], $authorization->headers());
        $this->assertSame(['api_key' => self::TOKEN], $authorization->query());
        $this->assertSame([self::TOKEN], $authorization->secrets());
    }

    public function test_it_is_empty_by_default(): void
    {
        $authorization = new HttpAuthorization();

        $this->assertSame([], $authorization->headers());
        $this->assertSame([], $authorization->query());
        $this->assertSame([], $authorization->secrets());
    }

    public function test_dumps_and_json_do_not_reveal_it(): void
    {
        $authorization = $this->authorization();

        ob_start();
        var_dump($authorization);
        $dumped = (string) ob_get_clean();

        foreach ([$dumped, print_r($authorization, true), (string) json_encode($authorization)] as $output) {
            $this->assertStringNotContainsString(self::TOKEN, $output);
        }

        $this->assertStringContainsString('Authorization', $dumped);
    }

    public function test_it_cannot_be_serialized(): void
    {
        $this->expectException(LogicException::class);

        serialize($this->authorization());
    }

    public function test_its_parameters_are_kept_out_of_stack_traces(): void
    {
        foreach ((new ReflectionMethod(HttpAuthorization::class, '__construct'))->getParameters() as $parameter) {
            $this->assertCount(1, $parameter->getAttributes(SensitiveParameter::class), $parameter->getName());
        }
    }

    public function test_it_refuses_a_header_without_a_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HttpAuthorization(headers: ['' => 'value']);
    }

    public function test_it_refuses_a_non_string_value_without_carrying_it(): void
    {
        try {
            /** @phpstan-ignore argument.type */
            new HttpAuthorization(query: ['api_key' => [self::TOKEN]]);
            $this->fail('A non-string value was accepted.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringNotContainsString(self::TOKEN, $exception->getMessage());
        }
    }

    public function test_it_refuses_secrets_that_are_not_a_list_of_strings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore argument.type */
        new HttpAuthorization(secrets: ['token' => self::TOKEN]);
    }

    private function authorization(): HttpAuthorization
    {
        return new HttpAuthorization(
            headers: ['Authorization' => 'Bearer ' . self::TOKEN],
            query: ['api_key' => self::TOKEN],
            secrets: [self::TOKEN],
        );
    }
}
