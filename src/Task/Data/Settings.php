<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class Settings implements TaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getInstructions(): ?string;
    abstract public function setInstructions(?string $instructions): self;
    abstract public function getSolution(): ?string;
    abstract public function setSolution(?string $solution): self;
}
