<?php

declare(strict_types=1);

namespace Fapost\Foundation\Solution\Enums;

/**
 * What asking an operator to connect a Solution led to.
 *
 * New outcomes may be added in a minor release: callers matching on it keep a default arm.
 *
 * @experimental
 */
enum SolutionConnectOutcome: string
{
    /** A trial started: the Solution is granted now. */
    case TrialStarted = 'trial_started';

    /** A request was sent; the operator decides. */
    case Requested = 'requested';

    /** A request for this Solution is already waiting for the operator; nothing new was sent. */
    case AlreadyPending = 'already_pending';

    /** Nothing changed: the operator does not offer it to this tenant now, with its reason. */
    case Refused = 'refused';
}
