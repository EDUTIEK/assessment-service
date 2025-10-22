<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class WriterAnnotation implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    abstract public function getResourceId(): int;
    abstract public function setResourceId(int $resource_id): self;
    abstract public function getMarkKey(): string;
    abstract public function setMarkKey(string $mark_key): self;
    abstract public function getMarkValue(): ?string;
    abstract public function setMarkValue(?string $mark_value): self;
    abstract public function getComment(): ?string;
    abstract public function setComment(?string $comment): self;
    abstract public function getParentNumber(): int;
    abstract public function setParentNumber(int $parent_number): self;
    abstract public function getStartPosition(): int;
    abstract public function setStartPosition(int $start_position): self;
    abstract public function getEndPosition(): int;
    abstract public function setEndPosition(int $end_position): self;
}
