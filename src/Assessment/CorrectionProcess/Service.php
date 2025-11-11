<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\Data\Writer;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos
    ) {
    }

    public function removeFinalization(Writer $writer)
    {
        // todo: determine the correct status (approcimation, stitch)
        // todo: log the action
        if ($writer->getCorrectionStatus() === CorrectionStatus::FINALIZED) {
            $writer->setCorrectionStatus(CorrectionStatus::OPEN);
            $writer->setCorrectionStatusChanged(new \DateTimeImmutable("now"));
            $writer->setCorrectionStatusChangedBy($this->user_id);
            $this->repos->writer()->save($writer);
        }
    }
}
