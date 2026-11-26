<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\DeepLink;

interface FullService
{
    /**
     * Get a link to the assessment in the backend
     */
    public function backend(int $ass_id, ?int $context_id = null, ?int $user_id = null);

    /**
     * Get a deep link to a correction for a corrector
     */
    public function correction(int $ass_id, ?int $context_id = null, ?int $user_id = null, ?int $task_id = null, ?int $writer_id = null);

    /**
     * Get the URL of the correction app with deep link
     */
    public function correctionApp(int $ass_id, ?int $context_id = null, ?int $user_id = null, ?int $task_id = null, ?int $writer_id = null);
}
