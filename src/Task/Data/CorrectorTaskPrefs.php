<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorTaskPrefs implements TaskEntity
{
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getCriterionCopy(): bool;
    abstract public function setCriterionCopy(bool $criterion_copy): self;
}
