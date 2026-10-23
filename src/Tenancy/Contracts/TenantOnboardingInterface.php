<?php

declare(strict_types=1);

namespace Fapost\Foundation\Tenancy\Contracts;

use Fapost\Foundation\Tenancy\Exceptions\TenantOnboardingException;

/**
 * The tenant's first-run setup wizard, as an operator controls it.
 *
 * Implemented by Core; operator packages only call it. A tenant whose onboarding is pending has its
 * console held in the setup wizard until its required steps are done; a restart puts a tenant there
 * again. Steps already done by the tenant's data (an assistant exists) stay done; steps that record a
 * confirmation (the content languages) are asked again.
 *
 * Restarting closes the console of a working tenant's staff until they finish the required steps: a
 * caller asks the operator to confirm first. Fast (one write in the tenant's schema): may be called
 * from an HTTP request.
 *
 * @experimental
 */
interface TenantOnboardingInterface
{
    /**
     * Puts the tenant back into the setup wizard. Restarting a tenant whose onboarding is already
     * pending succeeds and starts it over.
     *
     * @throws TenantOnboardingException when the tenant does not exist or is not Active
     */
    public function restart(string $tenantId): void;

    /**
     * Whether the tenant's setup wizard is pending (its console is held in the wizard).
     *
     * @throws TenantOnboardingException when the tenant does not exist or is not Active
     */
    public function isPending(string $tenantId): bool;
}
