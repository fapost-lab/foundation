<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use Fapost\Foundation\Quota\Contracts\LimitNoticeInterface;
use Fapost\Foundation\Quota\DTO\LimitNotice;
use Fapost\Foundation\Quota\Enums\LimitNoticeReason;
use PHPUnit\Framework\TestCase;

final class LimitNoticeReasonTest extends TestCase
{
    public function test_an_operator_can_word_the_notice_by_reason(): void
    {
        $operator = new class () implements LimitNoticeInterface {
            public function noticeFor(string $tenantId, string $key, string $locale, LimitNoticeReason $reason): ?LimitNotice
            {
                return match ($reason) {
                    LimitNoticeReason::Refused => new LimitNotice('Upgrade to create more.'),
                    LimitNoticeReason::Reached => null,
                };
            }
        };

        $this->assertSame('Upgrade to create more.', $operator->noticeFor('t', 'assistants', 'en', LimitNoticeReason::Refused)?->message);
        $this->assertNull($operator->noticeFor('t', 'assistants', 'en', LimitNoticeReason::Reached));
        $this->assertSame('reached', LimitNoticeReason::Reached->value);
    }
}
