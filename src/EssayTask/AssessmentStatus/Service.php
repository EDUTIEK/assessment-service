<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\Essay;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }
    public function hasComments()
    {
        return $this->repos->correctorComment()->hasByAssId($this->ass_id);
    }

    public function allWriterEssayStatus(): array
    {
        $writer_essays = [];
        foreach($this->repos->taskSettings()->allByAssId($this->ass_id) as $task) {
            foreach($this->repos->essay()->allByTaskId($task->getTaskId()) as $essay) {
                $writer_essays[$essay->getWriterId()][] = $essay;
            }
        }
        $writer_essay_status = [];
        foreach($writer_essays as $id => $essays) {
            $writer_essay_status[$id] = new WriterEssayStatus(
                $id,
                max(array_map(fn(Essay $e) => $e->getLastChange(), $essays)),
                max(array_map(fn(Essay $e) => $e->getPdfVersion(), $essays)) !== null
            );
        }
        return $writer_essay_status;
    }

    public function oneWriterEssayStatus(int $writer_id): ?WriterEssayStatus
    {
        $essays = $this->repos->essay()->allByWriterId($writer_id);

        return new WriterEssayStatus(
            $writer_id,
            max(array_map(fn(Essay $e) => $e->getLastChange(), $essays)),
            max(array_map(fn(Essay $e) => $e->getPdfVersion(), $essays)) !== null
        );
    }

    public function hasAuthorizedSummaries(?int $corrector_id = null)
    {
        return $this->repos->correctorSummary()->hasAuthorizedByAssId($this->ass_id, $corrector_id);
    }
}
