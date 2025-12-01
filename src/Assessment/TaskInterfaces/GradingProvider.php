<?php

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

interface GradingProvider
{
    /**
     * Get all available gradings for a task and a writer, indexed by integer position
     * Not available gradings are null
     * @return array<integer, ?Grading>
     */
    public function gradingsForTaskAndWriter(int $task_id, int $writer_id): array;
}
