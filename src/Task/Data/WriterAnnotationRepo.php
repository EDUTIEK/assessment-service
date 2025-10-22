<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface WriterAnnotationRepo
{
    public function new(): WriterAnnotation;
    public function one(int $id): ?WriterAnnotation;
    /** @return WriterAnnotation[] */
    public function allByTaskId(int $task_id): array;
    /** @return WriterAnnotation[] */
    public function allByWriterId(int $writer_id): array;
    public function save(WriterAnnotation $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
}
