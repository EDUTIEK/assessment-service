<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorPoints implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getKey(): string;
    abstract public function setKey(string $key): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    abstract public function getCommentId(): ?int;
    abstract public function setCommentId(?int $comment_id): self;
    abstract public function getCriterionId(): ?int;
    abstract public function setCriterionId(?int $criterion_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getPoints(): float;
    abstract public function setPoints(float $points): self;
}
