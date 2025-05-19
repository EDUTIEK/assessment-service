<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\GradeLevel;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\GradeLevel\FullService;
use Edutiek\AssessmentService\Assessment\Data\GradeLevel;
use Edutiek\AssessmentService\Assessment\Api\ApiException;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function all(): array
    {
        return $this->repos->gradeLevel()->allByAssId($this->ass_id);
    }

    public function new(): GradeLevel
    {
        return $this->repos->gradeLevel()->new()->setAssId($this->ass_id);
    }

    public function save(GradeLevel $grade_level) : void
    {
        $this->checkScope($grade_level);
        $this->repos->gradeLevel()->save($grade_level);
    }

    private function checkScope(GradeLevel $grade_level)
    {
        if ($grade_level->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}