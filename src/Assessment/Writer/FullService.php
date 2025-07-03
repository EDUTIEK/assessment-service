<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService extends ReadService
{
    /**
     * Get or create a writer of the assessment by its user id
     */
    public function getByUserId(int $user_id) : Writer;
}