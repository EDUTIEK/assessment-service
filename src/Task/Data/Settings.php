<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskType;
use Edutiek\AssessmentService\System\Api\HasHtml;

abstract class Settings implements TaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getPosition(): int;
    abstract public function setPosition(int $position): self;
    abstract public function getTaskType(): TaskType;
    abstract public function setTaskType(TaskType $type): self;
    abstract public function getTitle(): string;
    abstract public function setTitle(string $title): self;
    #[HasHtml]
    abstract public function getInstructions(): ?string;
    abstract public function setInstructions(?string $instructions): self;
    #[HasHtml]
    abstract public function getSolution(): ?string;
    abstract public function setSolution(?string $solution): self;

    public function getInfo(): TaskInfo
    {
        return new TaskInfo(
            $this->getTitle(),
            $this->getTaskType(),
            $this->getPosition(),
            $this->getTaskId()
        );
    }
}
