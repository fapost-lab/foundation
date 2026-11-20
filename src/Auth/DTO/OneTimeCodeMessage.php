<?php

declare(strict_types=1);

namespace Fapost\Foundation\Auth\DTO;

use Fapost\Foundation\Auth\Enums\OneTimeCodeChannel;
use InvalidArgumentException;
use LogicException;
use SensitiveParameter;

/**
 * One one-time code to deliver, as Core hands it to a
 * {@see \Fapost\Foundation\Auth\Contracts\OneTimeCodeSenderInterface}.
 *
 * The code lives in memory for the one call that delivers it:
 *
 * - it is private and read with {@see code()}, so `json_encode()` and a log context that encodes the object see
 *   only the other members;
 * - `var_dump()` and `print_r()` show it as `***` ({@see __debugInfo()});
 * - the constructor's `$code` is a `#[SensitiveParameter]`, so a stack trace does not carry it;
 * - `serialize()` throws, so the object cannot reach a queue, a cache or a session.
 *
 * `var_export()`, an `(array)` cast and Symfony's VarDumper still read private properties; do not pass this object
 * to any of them.
 *
 * @experimental
 */
final readonly class OneTimeCodeMessage
{
    private const string MASK = '***';

    /**
     * @param  string  $idempotencyKey  the same for every retry of one send; a new code gets a new key
     * @param  string  $tenantId  the tenant whose flow asks for the code
     * @param  OneTimeCodeChannel  $channel  the channel to deliver over
     * @param  string  $destination  a phone number in E.164 form for SMS, an email address for email
     * @param  string  $code  the digits the contact has to type back
     * @param  string  $locale  the contact's language, such as "uk"; the sender falls back to its own default
     * @param  string  $brandName  the name to sign the message with: the assistant the contact talks to
     * @param  int  $expiresInMinutes  how long the code is accepted, to tell the contact
     *
     * @throws InvalidArgumentException when a member is empty or the lifetime is not positive; the message never
     *                                  carries the code or the destination
     */
    public function __construct(
        public string $idempotencyKey,
        public string $tenantId,
        public OneTimeCodeChannel $channel,
        public string $destination,
        #[SensitiveParameter]
        private string $code,
        public string $locale,
        public string $brandName,
        public int $expiresInMinutes,
    ) {
        if ('' === $idempotencyKey || '' === $tenantId || '' === $destination || '' === $code || '' === $locale) {
            throw new InvalidArgumentException(
                'A one-time code message needs an idempotency key, a tenant id, a destination, a code and a locale.',
            );
        }

        if ($expiresInMinutes < 1) {
            throw new InvalidArgumentException('A one-time code must be accepted for at least one minute.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'idempotencyKey'   => $this->idempotencyKey,
            'tenantId'         => $this->tenantId,
            'channel'          => $this->channel,
            'destination'      => $this->destination,
            'code'             => self::MASK,
            'locale'           => $this->locale,
            'brandName'        => $this->brandName,
            'expiresInMinutes' => $this->expiresInMinutes,
        ];
    }

    /**
     * @throws LogicException always: a code is never serialized
     */
    public function __serialize(): array
    {
        throw new LogicException('A one-time code message holds a code and cannot be serialized.');
    }

    /**
     * @param  array<mixed>  $data
     *
     * @throws LogicException always: a code is never unserialized
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('A one-time code message cannot be unserialized.');
    }

    /**
     * The code to deliver. Do not log, store or queue it.
     */
    public function code(): string
    {
        return $this->code;
    }
}
