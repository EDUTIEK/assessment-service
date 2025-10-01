<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

use Closure;

/**
 * Provider of constraint checks
 */
interface Provider
{
    /**
     * Register a constraint
     * The constraint is created lazy when needed for an event
     *
     * @param class-string<Constraint> $constraint constraint class name
     * @param Closure(): Constraint $init constraint creation function
     */
    public function registerConstraint(string $constraint, Closure $init): void;

    /**
     * Call the registered constraints for an event
     */
    public function check(Action $action, ResultCollection $collection): void;
}
