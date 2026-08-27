<?php

declare(strict_types=1);

namespace Fapost\Foundation\Analytics\Contracts;

use Fapost\Foundation\Analytics\DTO\AnalyticsEvent;

interface AnalyticsWriterInterface
{
    public function record(AnalyticsEvent $event): void;
}
