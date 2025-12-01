<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

readonly class TaskInfo
{
    public function __construct(
        private string $title,
        private TaskType $task_type,
        private ?int $position = null,
        private ?int $id = null,
        private float $weight = 1,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTaskType(): TaskType
    {
        return $this->task_type;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function withId(int $id): self
    {
        return new self($this->title, $this->task_type, $this->position, $id);
    }

    public function withPosition(int $position): self
    {
        return new self($this->title, $this->task_type, $position, $this->id);
    }
}
