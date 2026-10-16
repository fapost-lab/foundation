<?php

declare(strict_types=1);

namespace Fapost\Foundation\Connector;

/**
 * What Core hands to one connector so it can register the channels it owns.
 *
 * Given by Core to one connector, once per process, before any boot(). It accepts only channel
 * types the connector declares in `extra.fapost.channels`; it refuses a type Core already serves
 * itself (a built-in channel such as `telegram`) and a type registered twice. Each violation is an
 * exception at boot.
 *
 * Since foundation 0.23, experimental.
 */
interface ConnectorRegistrarInterface
{
    /**
     * Register one channel the connector owns.
     */
    public function registerChannel(ConnectorChannel $channel): void;
}
