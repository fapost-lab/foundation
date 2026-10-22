<?php

declare(strict_types=1);

namespace Fapost\Foundation\Operator\Contracts;

use Fapost\Foundation\Operator\Enums\OperatorSurface;
use Illuminate\Http\Request;

/**
 * Answers whether this request may look at an operator screen.
 *
 * Implemented by Core, whose default answers for the people who run a `single` installation and
 * refuses everyone in `host` mode; an operator layer (the SaaS package) binds its own answer for its
 * operators. Packages that bring operator screens (monitoring) and Core's own gates only ask: none of
 * them has a sign-in of its own.
 *
 * The request is passed, not a user, because each layer knows a different sign-in: the implementation
 * reads the guard it owns. It must answer "no" for a surface it does not know, for a request without
 * a signed-in user, and whenever it cannot tell, and it must not throw to say "no". The caller may
 * still be serving a route outside the host rules (Core keeps operator routes off tenant hosts
 * itself), so the answer is about the person, not about the host.
 */
interface OperatorAccessInterface
{
    public function canView(Request $request, OperatorSurface $surface): bool;
}
