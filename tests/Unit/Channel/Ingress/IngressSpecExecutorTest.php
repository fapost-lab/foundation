<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Channel\Ingress;

use Fapost\Foundation\Channel\Ingress\IngressSpec;
use Fapost\Foundation\Channel\Ingress\IngressSpecExecutor;
use Fapost\Foundation\Channel\Ingress\SignedRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IngressSpecExecutorTest extends TestCase
{
    private const string BODY = '{"event":"message","message_token":4912661846655238145}';

    /**
     * Requests that would pass if an empty secret were a valid credential.
     *
     * @return array<string, array{IngressSpec, SignedRequest}>
     */
    public static function requestsForgeableWithAnEmptySecret(): array
    {
        return [
            'header_equals without the header' => [
                IngressSpec::headerEquals('x-telegram-bot-api-secret-token', 'tg:{channel}'),
                SignedRequest::create(self::BODY),
            ],
            'query_param without the parameter' => [
                IngressSpec::queryParam('token', 'q:{channel}'),
                SignedRequest::create(self::BODY),
            ],
            'hmac_sha256 signed with an empty key' => [
                IngressSpec::hmacSha256('x-viber-content-signature', 'viber:{channel}'),
                SignedRequest::create(self::BODY, ['X-Viber-Content-Signature' => hash_hmac('sha256', self::BODY, '')]),
            ],
            'hmac_sha1 signed with an empty key' => [
                IngressSpec::hmacSha1('x-sig', 's:{channel}', 'sha1='),
                SignedRequest::create(self::BODY, ['x-sig' => 'sha1=' . hash_hmac('sha1', self::BODY, '')]),
            ],
        ];
    }

    #[DataProvider('requestsForgeableWithAnEmptySecret')]
    public function test_an_empty_secret_refuses_the_request(IngressSpec $spec, SignedRequest $request): void
    {
        $this->assertFalse((new IngressSpecExecutor())->verify($spec, $request, ''));
    }

    public function test_an_empty_secret_does_not_affect_the_none_scheme(): void
    {
        $this->assertTrue((new IngressSpecExecutor())->verify(IngressSpec::none('n:{channel}'), SignedRequest::create(self::BODY), ''));
    }

    public function test_a_viber_signature_over_the_raw_body_is_verified(): void
    {
        $token    = '445da6az1s345z78-dazcczb2542zv51a-e0vc5fva17480im9';
        $spec     = IngressSpec::hmacSha256('x-viber-content-signature', 'viber:{channel}:{body.message_token}');
        $request  = SignedRequest::create(self::BODY, ['X-Viber-Content-Signature' => hash_hmac('sha256', self::BODY, $token)]);
        $executor = new IngressSpecExecutor();

        $this->assertTrue($executor->verify($spec, $request, $token));
        $this->assertSame('viber:channel-42:4912661846655238145', $executor->idempotencyKey($spec, $request, 'channel-42'));
    }
}
