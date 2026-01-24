<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

/**
 * Collector of constraint results for a checked action
 */
interface Collector
{
    /**
     * Call all providers to check if an action can be executed
     */
    public function check(Action $action): ConstraintResult;

    /**
     * Add a provider for constraint checks
     */
    public function addProvider(Provider $provider): void;

    /**
     * Remove a provider for constraint checks
     */
    public function removeProvider(Provider $provider): void;
}
