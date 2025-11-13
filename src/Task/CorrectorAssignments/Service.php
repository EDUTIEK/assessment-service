<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Task\Data\GradingStatus;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

readonly class Service implements FullService
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
            fn(CorrectorAssignment $x) => $count_assignments[$x->getWriterId()] = 1 + $count_assignments[$x->getWriterId()] ?? 0,
            $this->repos->correctorAssignment()->allByAssId($this->ass_id)
        );

        $missing = 0;
        foreach ($this->writer_service->all() as $writer) {
            // get only writers with authorized essays without exclusion
            if ((empty($writer->getWritingAuthorized())) || !empty($writer->getWritingExcluded())) {
                continue;
            }
            $assigned = $count_assignments[$writer->getId()] ?? 0;
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

    public function saveCorrectorFilter(int $corrector_id, ?array $grading_status, ?int $position): void
    {
        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $prefs->setFilterGradingStatus(
            $grading_status === null ? null : implode(
                ',',
                array_map(fn($status) => $status->value, $grading_status)
            )
        );

        $prefs->setFilterAssignedPosition($position);

        $this->repos->correctorPrefs()->save($prefs);
    }

    public function allByCorrectorIdFiltered(int $corrector_id): array
    {
        $assignments = $this->repos->correctorAssignment()->allByCorrectorId($corrector_id);

        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $pos = $prefs->getFilterAssignedPosition();
        $status = null;
        if ($prefs->getFilterGradingStatus() !== null) {
            $status = explode(',', $prefs->getFilterGradingStatus());
        }

        $filtered = [];
        foreach ($assignments as $assignment) {
            if ($pos !== null && $assignment->getPosition() !== $pos) {
                continue;
            }
            if ($status !== null) {
                $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                    $assignment->getTaskId(),
                    $assignment->getWriterId(),
                    $assignment->getCorrectorId()
                );
                $value = $summary?->getGradingStatus()?->value ?? GradingStatus::NOT_STARTED->value;

                if (!in_array($value, $status)) {
                    continue;
                }
            }
            $filtered[] = $assignment;
        }

        return $filtered;
    }
}
