<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Tenancy;

use Fapost\Foundation\Tenancy\DTO\AccessNotice;
use Fapost\Foundation\Tenancy\DTO\TenantAccessState;
use Fapost\Foundation\Tenancy\Enums\AccessMode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TenantAccessStateTest extends TestCase
{
    public function test_the_active_state_has_no_notice(): void
    {
        $state = TenantAccessState::active();

        $this->assertSame(AccessMode::Active, $state->mode);
        $this->assertNull($state->notice);
        $this->assertFalse($state->isStopped());
    }

    public function test_a_stopped_state_carries_its_notice(): void
    {
        $notice = new AccessNotice('Your trial has ended', 'Extend to keep your bot answering.', 'Extend', 'https://example.test/billing');
        $state = new TenantAccessState(AccessMode::Stopped, $notice);

        $this->assertTrue($state->isStopped());
        $this->assertSame('Extend', $state->notice?->actionLabel);
    }

    /**
     * @return array<string, array{string, ?string, ?string}>
     */
    public static function invalidNotices(): array
    {
        return [
            'empty title' => [' ', null, null],
            'label without url' => ['Ended', 'Extend', null],
            'url without label' => ['Ended', null, 'https://example.test'],
            'javascript url' => ['Ended', 'Extend', 'javascript:alert(1)'],
        ];
    }

    #[DataProvider('invalidNotices')]
    public function test_an_invalid_notice_is_refused(string $title, ?string $label, ?string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AccessNotice($title, null, $label, $url);
    }

    public function test_the_mode_values_are_stable(): void
    {
        $this->assertSame(['active', 'stopped'], array_map(static fn (AccessMode $m): string => $m->value, AccessMode::cases()));
    }
}
