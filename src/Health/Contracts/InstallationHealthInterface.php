<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\Contracts;

use Fapost\Foundation\Health\DTO\InstallationHealthReport;

/**
 * The health of the whole installation — live updates, webhook intake, queues — for a package that
 * renders it, such as an operator panel or a monitoring package.
 *
 * Implemented by Core; an operator layer may bind its own. Installation-wide and unfiltered by tenant,
 * so it does not decide who may see it: ask
 * {@see \Fapost\Foundation\Operator\Contracts\OperatorAccessInterface::canView()} with
 * {@see \Fapost\Foundation\Operator\Enums\OperatorSurface::Health} first. It needs no tenant context
 * and no request. A Core that predates it leaves it unbound: check `app()->bound()` and say the
 * figures are unavailable.
 *
 * Each probe's answer is cached for a few seconds, and a probe whose source is slow waits for its
 * timeout (a few seconds), so a call with a cold cache may block for several seconds. Call it when a
 * screen is rendered or polled, not on every request, and do not poll more often than every 5 seconds.
 *
 * @experimental
 */
interface InstallationHealthInterface
{
    /**
     * Never throws: a service whose health could not be read is reported as unknown, with the reason.
     */
    public function report(): InstallationHealthReport;
}
