<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface WriterCommentRepo
{
    public function new(): WriterComment;
    public function one(int $id): ?WriterComment;
    /** @return WriterComment[] */
    public function allByTaskId(int $task_id): array;
    /** @return WriterComment[] */
    public function allByWriterId(int $writer_id): array;
    public function save(WriterComment $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
}
