<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Channel\Ingress;

use Fapost\Foundation\Channel\Ingress\IngressHandshake;
use Fapost\Foundation\Channel\Ingress\IngressSpec;
use Fapost\Foundation\Channel\Ingress\IngressSpecExecutor;
use Fapost\Foundation\Channel\Ingress\IngressTimestamp;
use Fapost\Foundation\Channel\Ingress\SignatureScheme;
use Fapost\Foundation\Channel\Ingress\SignedRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IngressSpecExecutorV2Test extends TestCase
{
    private const string SECRET = '8f742231b10e8888abcd99yyyzzz85a5';

    private const string SLACK_BODY = 'token=xyzz0WbapA4vBCDEFasx0q6G&team_id=T1DC2JH3J&team_domain=testteamnow&channel_id=G8PSS9T3V&channel_name=foobar&user_id=U2CERLKJA&user_name=roadrunner&command=%2Fwebhook-collect&text=&response_url=https%3A%2F%2Fhooks.slack.com%2Fcommands%2FT1DC2JH3J%2F397700885554%2F96rGlfmibIGlgcZRskXaIFfN&trigger_id=398738663015.47445629121.803a0bc887a14d10d2c447fce8b6703c';

    private IngressSpecExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = new IngressSpecExecutor();
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function badTimestamps(): array
    {
        return [
            'missing'          => [null],
            'empty'            => [''],
            'non numeric'      => ['abc'],
            'negative'         => ['-5'],
            'decimal'          => ['1000.5'],
            'trailing newline' => ["1000\n"],
            '20 digits'        => ['00000000000000001000'],
            'int64 overflow'   => ['9223372036854775808'],
        ];
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function formBodies(): array
    {
        $json = '{"k":"v"}';

        return [
            'plus is a space'              => ['payload=' . '%7B%22k%22%3A%22a+b%22%7D', '{"k":"a b"}'],
            '%20 is a space'               => ['payload=%7B%22k%22%3A%22a%20b%22%7D', '{"k":"a b"}'],
            'uppercase and lowercase hex'  => ['payload=%7b%22k%22%3a%22v%22%7D', $json],
            'escaped plus stays a plus'    => ['payload=%7B%22k%22%3A%22a%2Bb%22%7D', '{"k":"a+b"}'],
            'invalid escape in the field'  => ['payload=%7B%22k%22%3A%22v%22%7D%zz', null],
            'invalid escape elsewhere'     => ['other=%zz&payload=%7B%22k%22%3A%22v%22%7D', $json],
            'first duplicate wins'         => ['payload=%7B%22k%22%3A%22v%22%7D&payload=%7B%22k%22%3A%22x%22%7D', $json],
            'invalid first duplicate'      => ['payload=%zz&payload=%7B%22k%22%3A%22v%22%7D', null],
            'semicolon is not a separator' => ['a=1;payload=%7B%22k%22%3A%22v%22%7D', null],
            'empty segments are skipped'   => ['&&payload=%7B%22k%22%3A%22v%22%7D&', $json],
            'encoded name'                 => ['pay%6Coad=%7B%22k%22%3A%22v%22%7D', $json],
            'value with an equals sign'    => ['payload=%7B%22k%22%3A%22a=b%22%7D', '{"k":"a=b"}'],
            'invalid utf-8 elsewhere'      => ["\xff\xfe=1&payload=%7B%22k%22%3A%22v%22%7D", $json],
            'scalar json is no document'   => ['payload=42', null],
            'no field'                     => ['other=1', null],
        ];
    }

    public function test_slacks_documented_example_is_reproduced(): void
    {
        $request = SignedRequest::create(self::SLACK_BODY, [
            'X-Slack-Request-Timestamp' => '1531420618',
            'X-Slack-Signature'         => 'v0=a2114d57b48eac39b9ad189dd8316235a7b4a8d21a10bd27519666489c69b503',
        ]);

        $this->assertTrue($this->executor->verify(IngressSpecV2Test::slack(), $request, self::SECRET, 1531420618));
        $this->assertFalse($this->executor->verify(IngressSpecV2Test::slack(), $request, 'another-secret', 1531420618));
    }

    public function test_a_tampered_body_fails_the_signed_template(): void
    {
        $good     = $this->slackRequest('{"a":1}', '1000');
        $tampered = SignedRequest::create('{"a":2}', $good->headers);

        $this->assertTrue($this->executor->verify(IngressSpecV2Test::slack(), $good, self::SECRET, 1000));
        $this->assertFalse($this->executor->verify(IngressSpecV2Test::slack(), $tampered, self::SECRET, 1000));
    }

    public function test_timestamp_boundaries(): void
    {
        $spec = IngressSpecV2Test::slack();
        $req  = $this->slackRequest('{"a":1}', '1000');

        $this->assertTrue($this->executor->verify($spec, $req, self::SECRET, 1300), 'exactly the tolerance');
        $this->assertFalse($this->executor->verify($spec, $req, self::SECRET, 1301), 'one past the tolerance');
        $this->assertTrue($this->executor->verify($spec, $req, self::SECRET, 700), 'future skew within tolerance');
        $this->assertFalse($this->executor->verify($spec, $req, self::SECRET, 699), 'future skew beyond tolerance');
    }

    #[DataProvider('badTimestamps')]
    public function test_a_bad_timestamp_header_fails(?string $timestamp): void
    {
        $spec             = new IngressSpec(SignatureScheme::HmacSha256, 'x-sig', '', 'k', '{header.t}:{raw}', new IngressTimestamp('t', 300));
        $headers          = null === $timestamp ? [] : ['t' => $timestamp];
        $headers['x-sig'] = hash_hmac('sha256', ($timestamp ?? '') . ':body', self::SECRET);

        $this->assertFalse($this->executor->verify($spec, SignedRequest::create('body', $headers), self::SECRET, 1000));
    }

    public function test_the_largest_int64_is_parsed_but_is_too_far_away(): void
    {
        $spec    = new IngressSpec(SignatureScheme::HmacSha256, 'x-sig', '', 'k', null, new IngressTimestamp('t', 300));
        $headers = ['t' => '9223372036854775807', 'x-sig' => hash_hmac('sha256', 'b', self::SECRET)];

        $this->assertFalse($this->executor->verify($spec, SignedRequest::create('b', $headers), self::SECRET, 1000));
    }

    public function test_leading_zeros_are_accepted(): void
    {
        $spec    = new IngressSpec(SignatureScheme::HmacSha256, 'x-sig', '', 'k', null, new IngressTimestamp('t', 300));
        $headers = ['t' => '0001000', 'x-sig' => hash_hmac('sha256', 'b', self::SECRET)];

        $this->assertTrue($this->executor->verify($spec, SignedRequest::create('b', $headers), self::SECRET, 1000));
    }

    public function test_without_now_the_current_time_is_used(): void
    {
        $spec    = new IngressSpec(SignatureScheme::HmacSha256, 'x-sig', '', 'k', null, new IngressTimestamp('t', 300));
        $headers = ['t' => (string) time(), 'x-sig' => hash_hmac('sha256', 'b', self::SECRET)];

        $this->assertTrue($this->executor->verify($spec, SignedRequest::create('b', $headers), self::SECRET));
    }

    #[DataProvider('formBodies')]
    public function test_the_form_parser(string $body, ?string $expectedJson): void
    {
        $spec = new IngressSpec(SignatureScheme::None, null, '', 'k', formField: 'payload');

        $this->assertSame(
            null === $expectedJson ? [] : json_decode($expectedJson, true),
            $this->executor->document($spec, SignedRequest::create($body)),
        );
    }

    public function test_a_json_body_is_preferred_over_the_form(): void
    {
        $spec = new IngressSpec(SignatureScheme::None, null, '', 'k', formField: 'payload');

        $this->assertSame(['payload' => 'x'], $this->executor->document($spec, SignedRequest::create('{"payload":"x"}')));
        $this->assertSame([], $this->executor->document($spec, SignedRequest::create('{}')));
    }

    public function test_without_a_form_field_a_form_body_gives_an_empty_document(): void
    {
        $this->assertSame([], $this->executor->document(IngressSpec::none('k'), SignedRequest::create('payload=%7B%7D')));
    }

    public function test_body_placeholders_read_the_form_document(): void
    {
        $spec    = new IngressSpec(SignatureScheme::None, null, '', 'slack:{channel}:{body.event_id|body.trigger_id}', formField: 'payload');
        $request = SignedRequest::create('payload=' . rawurlencode('{"trigger_id":"T1"}'));

        $this->assertSame('slack:c1:T1', $this->executor->idempotencyKey($spec, $request, 'c1'));
    }

    public function test_alternatives_take_the_first_non_empty_value(): void
    {
        $spec = IngressSpec::none('{body.a|body.b|header.x-id}:{body.c|body.d}');

        $this->assertSame('B:', $this->executor->idempotencyKey($spec, SignedRequest::create('{"a":"","b":"B"}'), 'c'));
        $this->assertSame('H:D', $this->executor->idempotencyKey($spec, SignedRequest::create('{"d":"D"}', ['X-Id' => 'H']), 'c'));
        $this->assertSame(':', $this->executor->idempotencyKey($spec, SignedRequest::create('{}'), 'c'));
    }

    public function test_raw_resolves_to_nothing_outside_the_signed_template(): void
    {
        $this->assertSame('k:', $this->executor->idempotencyKey(IngressSpec::none('k:{raw}'), SignedRequest::create('body'), 'c'));
    }

    public function test_the_signed_template_may_use_the_channel_header_and_literal_text(): void
    {
        $spec = new IngressSpec(SignatureScheme::HmacSha1, 'x-sig', 'sha1=', 'k', 'a|{header.t}|{raw}');
        $sig  = 'sha1=' . hash_hmac('sha1', 'a|7|body', self::SECRET);

        $this->assertTrue($this->executor->verify($spec, SignedRequest::create('body', ['t' => '7', 'x-sig' => $sig]), self::SECRET));
    }

    public function test_the_handshake_answers_a_matching_challenge_only(): void
    {
        $spec = IngressSpecV2Test::slack();

        $challenge = SignedRequest::create('{"type":"url_verification","challenge":"abc123"}');
        $event     = SignedRequest::create('{"type":"event_callback","challenge":"abc123"}');
        $case      = SignedRequest::create('{"type":"URL_VERIFICATION","challenge":"abc123"}');

        $this->assertSame('abc123', $this->executor->handshakeResponse($spec, $challenge));
        $this->assertNull($this->executor->handshakeResponse($spec, $event));
        $this->assertNull($this->executor->handshakeResponse($spec, $case));
        $this->assertNull($this->executor->handshakeResponse($spec, SignedRequest::create('payload=x')));
        $this->assertNull($this->executor->handshakeResponse(IngressSpec::none('k'), $challenge));
    }

    public function test_a_handshake_may_respond_with_an_empty_string(): void
    {
        $spec = new IngressSpec(SignatureScheme::None, null, '', 'k', handshake: new IngressHandshake('{body.type}', 'ping', '{body.missing}'));

        $this->assertSame('', $this->executor->handshakeResponse($spec, SignedRequest::create('{"type":"ping"}')));
    }

    public function test_version_one_behaviour_is_unchanged(): void
    {
        $spec = IngressSpec::hmacSha256('x-sig', 'k:{body.id}', 'sha256=');
        $body = '{"id":7}';
        $req  = SignedRequest::create($body, ['x-sig' => 'sha256=' . hash_hmac('sha256', $body, 's')]);

        $this->assertTrue($this->executor->verify($spec, $req, 's'));
        $this->assertSame('k:7', $this->executor->idempotencyKey($spec, $req, 'c'));
        $this->assertSame(['id' => 7], $req->body());
    }

    /**
     * @param  array<string, string>  $extra
     */
    private function slackRequest(string $body, string $timestamp, array $extra = []): SignedRequest
    {
        $signature = 'v0=' . hash_hmac('sha256', "v0:{$timestamp}:{$body}", self::SECRET);

        return SignedRequest::create($body, ['X-Slack-Request-Timestamp' => $timestamp, 'X-Slack-Signature' => $signature] + $extra);
    }
}
