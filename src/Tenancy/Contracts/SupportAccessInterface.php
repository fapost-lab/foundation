<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\DTO\SupportAccessGrant;
use Fapost\Foundation\Tenancy\DTO\SupportAccessRequest;
use Fapost\Foundation\Tenancy\Exceptions\SupportAccessUnavailableException;

/**
 * Lets a platform operator enter a tenant's admin panel as the tenant's platform support user.
 *
 * Implemented by Core; operator packages call it. It is off unless the platform enables it, so an
 * installation without an operator package offers no such entry. The caller decides who may enter;
 * Core only records the operator it is given.
 *
 * The grant is single use and short lived. The caller sends the browser to the grant's URL with a
 * POST carrying the token in its body, never in a URL, so it stays out of histories and logs.
 */
interface SupportAccessInterface
{
    /**
     * @throws SupportAccessUnavailableException when support access is off or the tenant cannot be entered
     */
    public function issue(SupportAccessRequest $request): SupportAccessGrant;
}
