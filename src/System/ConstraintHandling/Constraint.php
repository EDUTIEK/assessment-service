<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

interface Constraint
{
    /**
     * Get the events this constraint is prepared for
     * @return class-string[] events this constraint reacts on
     */
    public static function actions(): array;

    /**
     * Execute actions for a raised event
     * Parameters are taken from the specific event class
     */
    public function check(Action $action, ResultCollection $results): void;
}
