<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\GradeLevel;

use Edutiek\AssessmentService\Assessment\Data\GradeLevel;

interface FullService
{
    /** @return GradeLevel[] */
    public function all(): array;
    public function new(): GradeLevel;
    public function one(int $id): ?GradeLevel;
    public function save(GradeLevel $grade_level);
    public function delete(GradeLevel $grade_level): void;
}