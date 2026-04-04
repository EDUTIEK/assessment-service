<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface ContextInfoRepo
{
    /**
     * Get info about the embedding context of an assessment
     */
    public function get(int $context_id): ContextInfo;

    /**
     * Get a link to the assessment object for a user
     */
    public function link(int $ass_id, int $user_id): string;
}
