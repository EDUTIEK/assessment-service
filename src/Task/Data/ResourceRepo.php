<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface ResourceRepo
{
    public function new(): Resource;
    public function one(int $id): ?Resource;
    public function oneByTaskIdAndType(int $task_id, string $type): ?Resource;
    public function oneByFileId(string $file_id): ?Resource;
    /** @return Resource[] */
    public function allByTaskId(int $task_id): array;
    public function save(Resource $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
}
