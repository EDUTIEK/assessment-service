<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\Data\Writer;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function removeFinalization(Writer $writer)
    {
        // remove finalized status
        if (!empty($writer->getCorrectionFinalized())) {
            $writer->setCorrectionFinalized(null);
            $writer->setCorrectionFinalizedBy(null);
            $this->repos->writer()->save($writer);
        }
    }
}
