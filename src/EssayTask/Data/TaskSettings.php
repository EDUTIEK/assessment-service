<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class TaskSettings implements EssayTaskEntity
{
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getMaxPoints(): int;
    abstract public function setMaxPoints(int $max_points): self;
}
