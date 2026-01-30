<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WritingTask;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;

class Service implements ReadService
{
    private ?array $task_ids = null;
    private ?array $writer_ids = null;

    public function __construct(
        private readonly TaskManager $tasks,
        private readonly WriterService $writers
    ) {
    }

    public function all(): array
    {
        return $this->allByWriterIds($this->writerIds());
    }

    public function allByWriterIds(array $writer_ids): array
    {
        $allowed_writer_ids = $this->writerIds();

        $writing_tasks = [];
        foreach ($writer_ids as $writer_id) {
            if (in_array($writer_id, $allowed_writer_ids)) {
                foreach ($this->taskIds() as $task_id) {
                    $writing_tasks[] = new WritingTask($writer_id, $task_id);
                }
            }
        }
        return $writing_tasks;
    }

    private function writerIds(): array
    {
        return $this->writer_ids ?? $this->writers->allIds();
    }

    private function taskIds(): array
    {
        return $this->task_ids ?? $this->tasks->allIds();
    }
}
