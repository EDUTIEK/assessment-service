<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class RatingCriterion implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTitle(): string;
    abstract public function setTitle(string $title): self;
    abstract public function getDescription(): ?string;
    abstract public function setDescription(?string $description): self;
    abstract public function getPoints(): int;
    abstract public function setPoints(int $points): self;
    abstract public function getCorrectorId(): ?int;
    abstract public function setCorrectorId(?int $corrector_id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getGeneral(): int;
    abstract public function setGeneral(int $general): self;
}
