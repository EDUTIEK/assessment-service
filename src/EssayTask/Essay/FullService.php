<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Essay;

use Edutiek\AssessmentService\EssayTask\Data\Essay;

interface FullService
{
    /** @return Essay[] */
    public function allByWriterId(int $writer_id): array;
    /** @return Essay[] */
    public function allByTaskId(int $task_id): array;
    /**
     * Get all essays for a writer and all tasks (create if not existing)
     * @return Essay[], indexed by task id
     */
    public function getByWriterId(int $writer_id): array;
    /**
     * Get an essay for a writer and task (create if not existing)
     */
    public function getByWriterIdAndTaskId(int $writer_id, int $task_id): Essay;
    /**
     * Get the essay of a writer and task (don't create)
     */
    public function oneByWriterIdAndTaskId(int $writer_id, int $task_id): ?Essay;
    public function new(int $writer_id, int $task_id): Essay;
    public function save(Essay $essay);
}
