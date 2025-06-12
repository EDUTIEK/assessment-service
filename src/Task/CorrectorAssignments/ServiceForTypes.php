<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\EssayTask\TaskInterfaces\CorrectorAssignment as ForTypesService;
use Edutiek\AssessmentService\Task\Data\Repositories;

class ServiceForTypes implements ForTypesService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function all(): array
    {
        return $this->repos->correctorAssignment()->allByAssId($this->ass_id);
    }
}
