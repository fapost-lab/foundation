<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Channel\Ingress;

use Fapost\Foundation\Channel\Ingress\IngressHandshake;
use Fapost\Foundation\Channel\Ingress\IngressSpec;
use Fapost\Foundation\Channel\Ingress\IngressTimestamp;
use Fapost\Foundation\Channel\Ingress\SignatureScheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

final class IngressSpecV2Test extends TestCase
{
    public static function slack(): IngressSpec
    {
        return new IngressSpec(
            SignatureScheme::HmacSha256,
            'x-slack-signature',
            'v0=',
            'slack:{channel}:{body.event_id|body.trigger_id}',
            'v0:{header.x-slack-request-timestamp}:{raw}',
            new IngressTimestamp('x-slack-request-timestamp', 300),
            'payload',
            new IngressHandshake('{body.type}', 'url_verification', '{body.challenge}'),
        );
    }

    /**
     * @return array<string, array{callable(): mixed}>
     */
    public static function invalidSpecs(): array
    {
        $hmac = SignatureScheme::HmacSha256;

        return [
            'signed without raw'        => [fn () => new IngressSpec($hmac, 'x', '', 'k', 'v0:{header.t}')],
            'signed with raw twice'     => [fn () => new IngressSpec($hmac, 'x', '', 'k', '{raw}{raw}')],
            'signed empty'              => [fn () => new IngressSpec($hmac, 'x', '', 'k', '')],
            'signed with header_equals' => [fn () => new IngressSpec(SignatureScheme::HeaderEquals, 'x', '', 'k', '{raw}')],
            'timestamp with none'       => [fn () => new IngressSpec(SignatureScheme::None, null, '', 'k', null, new IngressTimestamp('t', 5))],
            'tolerance zero'            => [fn () => new IngressTimestamp('t', 0)],
            'tolerance too large'       => [fn () => new IngressTimestamp('t', 3601)],
            'timestamp header empty'    => [fn () => new IngressTimestamp('', 5)],
            'form empty'                => [fn () => new IngressSpec($hmac, 'x', '', 'k', null, null, '')],
            'form with a space'         => [fn () => new IngressSpec($hmac, 'x', '', 'k', null, null, 'a b')],
            'form with a newline'       => [fn () => new IngressSpec($hmac, 'x', '', 'k', null, null, "payload\n")],
            'form too long'             => [fn () => new IngressSpec($hmac, 'x', '', 'k', null, null, str_repeat('a', 65))],
            'handshake empty when'      => [fn () => new IngressHandshake('', 'a', 'b')],
            'handshake empty equals'    => [fn () => new IngressHandshake('a', '', 'b')],
            'handshake empty respond'   => [fn () => new IngressHandshake('a', 'b', '')],
        ];
    }

    public function test_a_version_one_spec_serialises_without_the_new_keys(): void
    {
        $spec = IngressSpec::headerEquals('x-telegram-bot-api-secret-token', 'tg:{channel}:{body.update_id}');

        $this->assertSame(1, $spec->wireVersion());
        $this->assertSame(
            '{"v":1,"scheme":"header_equals","parameter":"x-telegram-bot-api-secret-token","prefix":"","idempotency":"tg:{channel}:{body.update_id}"}',
            json_encode($spec),
        );
    }

    public function test_a_version_two_spec_round_trips(): void
    {
        $spec = self::slack();
        $wire = json_decode((string) json_encode($spec), true);

        $this->assertSame(2, $spec->wireVersion());
        $this->assertSame([
            'v'           => 2,
            'scheme'      => 'hmac_sha256',
            'parameter'   => 'x-slack-signature',
            'prefix'      => 'v0=',
            'idempotency' => 'slack:{channel}:{body.event_id|body.trigger_id}',
            'signed'      => 'v0:{header.x-slack-request-timestamp}:{raw}',
            'timestamp'   => ['header' => 'x-slack-request-timestamp', 'tolerance' => 300],
            'form'        => 'payload',
            'handshake'   => ['when' => '{body.type}', 'equals' => 'url_verification', 'respond' => '{body.challenge}'],
        ], $wire);
        $this->assertEquals($spec, IngressSpec::fromArray($wire));
    }

    public function test_an_idempotency_alternative_alone_makes_a_version_two_spec_with_null_keys(): void
    {
        $spec = IngressSpec::hmacSha256('x-sig', 'k:{body.a|body.b}');
        $wire = $spec->jsonSerialize();

        $this->assertSame(2, $wire['v']);
        $this->assertNull($wire['signed']);
        $this->assertNull($wire['timestamp']);
        $this->assertNull($wire['form']);
        $this->assertNull($wire['handshake']);
        $this->assertEquals($spec, IngressSpec::fromArray($wire));
    }

    public function test_a_pipe_outside_a_placeholder_does_not_bump_the_version(): void
    {
        $this->assertSame(1, IngressSpec::none('a|b:{channel}')->wireVersion());
    }

    public function test_version_one_with_a_new_key_and_unknown_versions_are_rejected(): void
    {
        $base = ['scheme' => 'hmac_sha256', 'parameter' => 'x', 'prefix' => '', 'idempotency' => 'k'];

        foreach (['form' => 'payload', 'signed' => '{raw}', 'timestamp' => ['header' => 'h', 'tolerance' => 5], 'handshake' => ['when' => 'a', 'equals' => 'b', 'respond' => 'c']] as $key => $value) {
            try {
                IngressSpec::fromArray(['v' => 1, $key => $value] + $base);
                $this->fail("v1 with {$key} was accepted");
            } catch (ValueError) {
                $this->addToAssertionCount(1);
            }
        }

        $this->expectException(ValueError::class);
        IngressSpec::fromArray(['v' => 3] + $base);
    }

    #[DataProvider('invalidSpecs')]
    public function test_invalid_combinations_are_rejected(callable $build): void
    {
        $this->expectException(ValueError::class);
        $build();
    }

    public function test_the_tolerance_bounds_are_accepted(): void
    {
        $this->assertSame(1, (new IngressTimestamp('t', 1))->toleranceSeconds);
        $this->assertSame(3600, (new IngressTimestamp('t', 3600))->toleranceSeconds);
    }

    public function test_malformed_version_two_fields_are_rejected(): void
    {
        $this->expectException(ValueError::class);
        IngressSpec::fromArray(['v' => 2, 'scheme' => 'hmac_sha256', 'parameter' => 'x', 'idempotency' => 'k', 'timestamp' => ['header' => 'h', 'tolerance' => '5']]);
    }
}
