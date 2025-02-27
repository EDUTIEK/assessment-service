<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Manager;

interface FullService
{
    /**
     * Create a new assessment
     */
    public function create(): void;

    /**
     * Delete an assessment
     */
    public function delete(): void;

    /**
     * Clone an assessment
     */
    public function clone(int $new_ass_id): void;

}