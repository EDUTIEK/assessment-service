<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface EssayRepo
{
    public function new(): Essay;
    public function one(int $id): ?Essay;
    public function oneByWriterIdAndTaskId(int $writer_id, int $task_id): ?Essay;
    /** @return Essay[] */
    public function allByAssId(int $ass_id): array;
    /** @return Essay[] */
    public function allByTaskId(int $task_id): array;
    /** @return Essay[] */
    public function allByWriterId(int $writer_id): array;
    public function save(Essay $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByWriterId(int $writer_id): void;
}