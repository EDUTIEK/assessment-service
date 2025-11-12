<?php

namespace Edutiek\AssessmentService\Task\AssessmentStatus;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WritingStatus;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentsService;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WriterService $writer_service,
        private CorrectorAssignmentsService $assignment_service,
    ) {
    }
    public function hasComments()
    {
        return $this->repos->correctorComment()->hasByAssId($this->ass_id);
    }

    public function allWriterCombinedStatus(): array
    {
        $essay_writer_mapping = [];
        $writer_summaries = [];
        $status = [];

        foreach ($this->repos->correctorSummary()->allByAssId($this->ass_id) as $summary) {

            $writer_summaries[$summary->getWriterId()][] = $summary;
        }

        foreach ($this->writer_service->all() as $writer) {
            $summaries = $writer_summaries[$writer->getId()] ?? [];
            $status[$writer->getId()] = $this->getWriterCombinedStatus($writer, $summaries);
        }
        return $status;
    }

    public function oneWriterCombinedStatus(Writer $writer): CombinedStatus
    {
        $summaries = $this->repos->correctorSummary()->allByWriterId($writer->getId());
        return $this->getWriterCombinedStatus($writer, $summaries);
    }

    /**
     * @param Writer                $writer
     * @param CorrectorSummary[]   $summaries
     * @return CombinedStatus
     */
    private function getWriterCombinedStatus(Writer $writer, array $summaries): CombinedStatus
    {
        $writing_status = $writer->getWritingStatus();
        if ($writing_status !== WritingStatus::AUTHORIZED) {
            return CombinedStatus::from($writing_status->value);
        }

        if ($writer->getCorrectionFinalized() !== null) {
            return CombinedStatus::FINALIZED;
        }

        if ($writer->getStitchNeeded()) {
            return CombinedStatus::STITCH_NEEDED;
        }

        if (count($summaries) > 0 && max(array_map(fn(CorrectorSummary $s) => $s->getLastChange(), $summaries)) !== null) {
            return CombinedStatus::STARTED;
        }

        return CombinedStatus::WRITING_AUTHORIZED;
    }


    public function hasAuthorizedSummaries(?int $corrector_id = null)
    {
        return $this->repos->correctorSummary()->hasAuthorizedByAssId($this->ass_id, $corrector_id);
    }

    public function allCorrectorCorrectionSummaries(?array $corrector_ids = null): array
    {
        $assignments_by_corrector = [];
        $summaries_by_corrector = [];

        foreach ($this->assignment_service->all() as $assignment) {
            $assignments_by_corrector[$assignment->getCorrectorId()][] = $assignment;
        }
        foreach ($this->repos->correctorSummary()->allByAssId($this->ass_id) as $summary) {
            $summaries_by_corrector[$summary->getCorrectorId()][] = $summary;
        }

        if ($corrector_ids === null) {
            $corrector_ids = array_unique(array_merge(array_keys($assignments_by_corrector), array_keys($summaries_by_corrector)));
        }

        $correction_summaries = [];
        foreach ($corrector_ids as $id) {
            $correction_summaries[$id] = $this->getCorrectorCorrectionSummary(
                $id,
                $assignments_by_corrector[$id] ?? [],
                $summaries_by_corrector[$id] ?? []
            );
        }
        return $correction_summaries;
    }

    public function oneCorrectorCorrectionSummary(int $corrector_id): CorrectorCorrectionSummary
    {
        return $this->getCorrectorCorrectionSummary(
            $corrector_id,
            $this->assignment_service->allByCorrectorId($corrector_id),
            $this->repos->correctorSummary()->allByCorrectorId($corrector_id)
        );
    }

    /**
     * @param CorrectorAssignment[] $corrector_assignments
     * @param CorrectorSummary[] $corrector_summaries
     * @return CorrectorCorrectionSummary
     */
    private function getCorrectorCorrectionSummary(int $corrector_id, array $corrector_assignments, array $corrector_summaries): CorrectorCorrectionSummary
    {
        return new CorrectorCorrectionSummary(
            $corrector_id,
            count(array_filter($corrector_assignments, fn(CorrectorAssignment $ass) => $ass->getPosition() === 0)),
            count(array_filter($corrector_assignments, fn(CorrectorAssignment $ass) => $ass->getPosition() === 1)),
            count($corrector_assignments) - count($corrector_summaries),
            $authorized = count(array_filter($corrector_summaries, fn(CorrectorSummary $sum) => $sum->getCorrectionAuthorized() !== null)),
            count($corrector_summaries) - $authorized
        );
    }

    public function getCorrectorsWithOpenAuthorizations()
    {
        // TODO: Implement getCorrectorsWithOpenAuthorizations() method.
    }
}
