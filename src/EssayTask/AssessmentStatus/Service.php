<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\EssayTask\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WritingStatus;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WriterService $writer_service
    ) {
    }
    public function hasComments()
    {
        return $this->repos->correctorComment()->hasByAssId($this->ass_id);
    }

    public function allWriterEssaySummaries(): array
    {
        $writer_essays = [];
        foreach ($this->repos->taskSettings()->allByAssId($this->ass_id) as $task) {
            foreach ($this->repos->essay()->allByTaskId($task->getTaskId()) as $essay) {
                $writer_essays[$essay->getWriterId()][] = $essay;
            }
        }
        $writer_essay_status = [];
        foreach ($writer_essays as $id => $essays) {
            $writer_essay_status[$id] = new WriterEssaySummary(
                $id,
                max(array_map(fn (Essay $e) => $e->getLastChange(), $essays)),
                max(array_map(fn (Essay $e) => $e->getPdfVersion(), $essays)) !== null,
                array_sum(array_map(fn (Essay $e) => $e->getWordCount(), $essays))
            );
        }
        return $writer_essay_status;
    }

    public function oneWriterEssaySummary(int $writer_id): ?WriterEssaySummary
    {
        $essays = $this->repos->essay()->allByWriterId($writer_id);

        return new WriterEssaySummary(
            $writer_id,
            max(array_map(fn (Essay $e) => $e->getLastChange(), $essays)),
            max(array_map(fn (Essay $e) => $e->getPdfVersion(), $essays)) !== null,
            array_sum(array_map(fn (Essay $e) => $e->getWordCount(), $essays))
        );
    }

    public function allWriterCorrectionStatus() : array
    {
        $essay_writer_mapping = [];
        $writer_summaries = [];
        $status = [];

        foreach ($this->repos->essay()->allByAssId($this->ass_id) as $essay) {
            $essay_writer_mapping[$essay->getId()] = $essay->getWriterId();
        }

        foreach ($this->repos->correctorSummary()->allByAssId($this->ass_id) as $summary) {
            $writer_id = $essay_writer_mapping[$summary->getEssayId()]??-1;
            $writer_summaries[$writer_id][] = $summary;
        }

        foreach ($this->writer_service->all() as $writer) {
            $summaries = $writer_summaries[$writer->getId()] ?? [];
            $status[$writer->getId()] = $this->getWriterCorrectionStatus($writer, $summaries);
        }
        return $status;
    }

    public function oneWriterCorrectionStatus(Writer $writer) : CorrectionStatus
    {
        $summaries = $this->repos->correctorSummary()->allByWriterId($writer->getId());
        return $this->getWriterCorrectionStatus($writer, $summaries);
    }

    /**
     * @param Writer                $writer
     * @param Essay[]            $essay
     * @param CorrectorSummary[]   $summaries
     * @return CorrectionStatus
     */
    private function getWriterCorrectionStatus(Writer $writer, array $summaries) : CorrectionStatus
    {
        $writing_status = $writer->getStatus();
        if ($writing_status !== WritingStatus::AUTHORIZED) {
            return CorrectionStatus::from($writing_status->value);
        }

        if ($writer->getCorrectionFinalized() !== null) {
            return CorrectionStatus::FINALIZED;
        }

        if (!$writer->getStitchNeeded()) {
            return CorrectionStatus::STITCH_NEEDED;
        }

        if (count($summaries) > 0 && max(array_map(fn (CorrectorSummary $s) => $s->getLastChange(), $summaries)) !== null) {
            return CorrectionStatus::STARTED;
        }

        return CorrectionStatus::WRITING_AUTHORIZED;
    }


    public function hasAuthorizedSummaries(?int $corrector_id = null)
    {
        return $this->repos->correctorSummary()->hasAuthorizedByAssId($this->ass_id, $corrector_id);
    }
}
