<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;

abstract class CorrectorAssignment implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getPosition(): GradingPosition;
    abstract public function setPosition(GradingPosition $position): self;
}
