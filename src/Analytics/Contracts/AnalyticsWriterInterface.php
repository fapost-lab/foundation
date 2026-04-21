<?php

declare(strict_types=1);

namespace FAPost\Foundation\Analytics\Contracts;

use FAPost\Foundation\Analytics\DTO\AnalyticsEvent;

interface AnalyticsWriterInterface
{
    public function record(AnalyticsEvent $event): void;
}
