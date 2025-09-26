<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Checks;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private WriterService $writer,
        private CorrectorService $corrector,
        private Repositories $repos,
    ) {
    }

    public function hasTask(int $task_id): bool
    {
        return $this->repos->settings()->has($this->ass_id, $task_id);
    }

    public function hasWriter(int $writer_id): bool
    {
        return $this->writer->has($writer_id);
    }

    public function hasCorrector(int $corrector_id): bool
    {
        return $this->corrector->has($corrector_id);
    }

    public function isAssigned(int $writer_id, int $corrector_id, int $task_id): bool
    {
        return $this->repos->correctorAssignment()->hasByIds($writer_id, $corrector_id, $task_id);
    }
}
