<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface TaskSettingsRepo
{
    public function new(): TaskSettings;
    public function one(int $task_id): ?TaskSettings;
    public function save(TaskSettings $entity): void;
    public function delete(int $task_id): void;
}