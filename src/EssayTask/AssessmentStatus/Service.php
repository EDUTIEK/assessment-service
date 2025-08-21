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
}
