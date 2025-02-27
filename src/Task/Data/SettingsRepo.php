<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface SettingsRepo
{
    public function new(): Settings;
    public function one(int $task_id): ?Settings;
    public function countByAssId(int $ass_id): int;
    /** @return Settings[] */
    public function allByAssId(int $ass_id): array;
    public function save(Settings $entity): void;
    public function delete(int $task_id): void;
    public function deleteByAssId(int $ass_id): void;
}
