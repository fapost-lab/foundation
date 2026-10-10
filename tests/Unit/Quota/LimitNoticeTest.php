<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Quota;

use Fapost\Foundation\Quota\DTO\LimitNotice;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LimitNoticeTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string, ?string}>
     */
    public static function invalidNotices(): array
    {
        return [
            'empty message'     => [' ', null, null],
            'label without url' => ['Upgrade', 'Upgrade', null],
            'url without label' => ['Upgrade', null, 'https://example.test'],
            'javascript url'    => ['Upgrade', 'Upgrade', 'javascript:alert(1)'],
        ];
    }

    public function test_a_notice_without_an_action(): void
    {
        $notice = new LimitNotice('Upgrade to Pro to raise it.');

        $this->assertSame('Upgrade to Pro to raise it.', $notice->message);
        $this->assertNull($notice->actionLabel);
        $this->assertNull($notice->actionUrl);
    }

    public function test_a_notice_with_an_action(): void
    {
        $notice = new LimitNotice('Upgrade to Pro.', 'Upgrade', 'https://example.test/billing');

        $this->assertSame('Upgrade', $notice->actionLabel);
        $this->assertSame('https://example.test/billing', $notice->actionUrl);
    }

    #[DataProvider('invalidNotices')]
    public function test_an_invalid_notice_is_rejected(string $message, ?string $label, ?string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitNotice($message, $label, $url);
    }
}
