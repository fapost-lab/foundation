<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tests\Unit\Channel\Settings;

use Fapost\Foundation\Channel\InboundEventIgnoredException;
use Fapost\Foundation\Channel\Settings\ChannelSetupCopyKind;
use Fapost\Foundation\Channel\Settings\ChannelSetupGuideInterface;
use Fapost\Foundation\Channel\Settings\ChannelSetupStep;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ChannelSetupStepTest extends TestCase
{
    public function test_a_step_defaults_to_plain_text_copy(): void
    {
        $step = new ChannelSetupStep('Open the app settings');

        $this->assertNull($step->text);
        $this->assertNull($step->copy);
        $this->assertNull($step->translationKey);
        $this->assertSame(ChannelSetupCopyKind::Text, $step->copyKind);
    }

    public function test_the_copy_kinds_have_stable_values(): void
    {
        $this->assertSame(['text', 'url', 'code'], array_map(fn (ChannelSetupCopyKind $k) => $k->value, ChannelSetupCopyKind::cases()));
    }

    public function test_an_empty_title_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ChannelSetupStep('');
    }

    public function test_a_guide_may_be_pure_and_return_steps(): void
    {
        $guide = new class () implements ChannelSetupGuideInterface {
            public function setupGuide(?string $webhookUrl, array $config): array
            {
                return [new ChannelSetupStep('Paste the URL', copy: $webhookUrl, copyKind: ChannelSetupCopyKind::Url, translationKey: 'slack.setup.url')];
            }
        };

        $this->assertNull($guide->setupGuide(null, [])[0]->copy);
        $this->assertSame('https://x.test/h', $guide->setupGuide('https://x.test/h', [])[0]->copy);
    }

    public function test_the_ignored_event_exception_is_a_runtime_exception(): void
    {
        $this->assertInstanceOf(RuntimeException::class, new InboundEventIgnoredException('bot message'));
    }
}
