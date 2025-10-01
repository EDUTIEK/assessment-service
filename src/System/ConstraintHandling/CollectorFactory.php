<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

/**
 * Factory for result collector
 */
interface CollectorFactory
{
    /**
     * Get a results collector of constraints
     * @param int $ass_id   ID of the assessment for which the constraints are checked
     * @param int $user     ID of the currenty active user
     */
    public function collector(int $ass_id, int $user_id): Collector;
}
