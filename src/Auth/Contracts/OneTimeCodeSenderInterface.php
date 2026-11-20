<?php

declare(strict_types=1);

namespace Fapost\Foundation\Auth\Contracts;

use Fapost\Foundation\Auth\DTO\OneTimeCodeDelivery;
use Fapost\Foundation\Auth\DTO\OneTimeCodeMessage;
use Fapost\Foundation\Auth\Enums\OneTimeCodeChannel;

/**
 * Delivers a one-time code that a flow's `auth_request` node asks a contact to type back. Implemented by Core
 * (email) and by packages: an SMS connector, an operator package that sends mail its own way.
 *
 * Core generates the code, keeps only its keyed hash, counts the attempts and limits how often and to whom codes
 * are sent; the sender only delivers the text it is given, on its own queue-less call. Core calls it from a queued
 * job that runs inside the tenant of the message.
 *
 * Implementation
 * - MUST: deliver {@see OneTimeCodeMessage::code()} to {@see OneTimeCodeMessage::$destination} with a fixed text
 *   of its own, in {@see OneTimeCodeMessage::$locale} where it can; be idempotent by
 *   {@see OneTimeCodeMessage::$idempotencyKey} where its provider supports it (Core already guards a retried job);
 *   report a refusal or a provider error as {@see OneTimeCodeDelivery::failed()} rather than throw.
 * - MUST NOT: log, store, queue or put into an exception message the code, or the message object that holds it;
 *   send anything else to the destination; keep the message between calls; hold tenant data or a request- or
 *   job-scoped service in its properties — a registered sender lives as long as the worker and serves every tenant,
 *   so it reads per-tenant settings (credentials, a sender name) inside {@see send()}.
 *
 * @experimental
 */
interface OneTimeCodeSenderInterface
{
    /**
     * The channel this sender delivers over; one sender serves a channel at a time.
     */
    public function channel(): OneTimeCodeChannel;

    public function send(OneTimeCodeMessage $message): OneTimeCodeDelivery;
}
