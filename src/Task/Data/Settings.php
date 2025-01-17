<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class Settings implements TaskEntity
{
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
    public abstract function getInstructions(): ?string;
    public abstract function setInstructions(?string $instructions): self;
    public abstract function getSolution(): ?string;
    public abstract function setSolution(?string $solution): self;
}
