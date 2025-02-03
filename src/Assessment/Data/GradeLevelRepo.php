<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface GradeLevelRepo
{
    public function new(): GradeLevel;
    public function one(int $id): ?GradeLevel;
    /** @return GradeLevel[] */
    public function allByAssId(int $ass_id): array;
    public function save(GradeLevel $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
}
