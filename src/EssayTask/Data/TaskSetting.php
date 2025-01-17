<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class TaskSetting implements EssayTaskEntity
{
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): self;
    public abstract function getMaxPoints(): int;
    public abstract function setMaxPoints(int $max_points): self;
}
