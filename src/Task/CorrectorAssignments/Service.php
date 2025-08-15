<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\EssayTask\TaskInterfaces\CorrectorAssignment as ForTypesService;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private CorrectionSettings $correction_settings,
        private WriterService $writer_service,
        private Repositories $repos
    ) {
    }

    public function all(): array
    {
        return $this->repos->correctorAssignment()->allByAssId($this->ass_id);
    }

    public function allByWriterId(int $writer_id): array
    {
        return $this->repos->correctorAssignment()->allByWriterId($writer_id);
    }

    public function countMissingCorrectors(): int
    {
        $required = $this->correction_settings->getRequiredCorrectors();
        $count_assignments = [];
        array_map(
            fn(CorrectorAssignment $x) => $count_assignments[$x->getWriterId()] = 1+$count_assignments[$x->getWriterId()]??0,
            $this->repos->correctorAssignment()->allByAssId($this->ass_id)
        );

        $missing = 0;
        foreach ($this->writer_service->all() as $writer) {
            // get only writers with authorized essays without exclusion
            if ((empty($writer->getWritingAuthorized())) || !empty($writer->getWritingExcluded())) {
                continue;
            }
            $assigned = $count_assignments[$writer->getId()]??0;
            $missing += max(0, $required - $assigned);
        }
        return $missing;
    }

    public function allByCorrectorId(int $corrector_id): array
    {
        return $this->repos->correctorAssignment()->allByCorrectorId($corrector_id);
    }

    public function oneById(int $id): ?CorrectorAssignment
    {
        return $this->repos->correctorAssignment()->oneById($id);
    }
}
