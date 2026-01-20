<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Manager;

interface FullService
{
    /**
     * Create a new assessment
     */
    public function create(bool $multi_tasks): void;

    /**
     * Delete an assessment
     */
    public function delete(): void;

    /**
     * Clone an assessment
     */
    public function clone(int $new_ass_id): void;

}
