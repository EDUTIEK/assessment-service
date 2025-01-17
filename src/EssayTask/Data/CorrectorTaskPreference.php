<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorTaskPreference implements EssayTaskEntity
{
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): self;
    public abstract function getCriterionCopy(): bool;
    public abstract function setCriterionCopy(bool $criterion_copy): self;
}
