<?php

declare(strict_types=1);

namespace Fapost\Foundation\Health\Enums;

/**
 * A service of the installation whose health {@see \Fapost\Foundation\Health\Contracts\InstallationHealthInterface}
 * reports.
 *
 * New services may be added in a minor release; a renderer branches on them with a default arm.
 *
 * @experimental
 */
enum MonitoredService: string
{
    /** The websocket server (Laravel Reverb) behind live updates. */
    case Reverb = 'reverb';

    /** The webhook ingress gateway. */
    case Gateway = 'gateway';

    /** The webhook routing cache, compared with the registry it is built from. */
    case WebhookRegistry = 'webhook_registry';

    /** The named queues: wait, throughput and failed jobs. */
    case Queues = 'queues';
}
