<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class WriterComment implements TaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
    public abstract function getComment(): ?string;
    public abstract function setComment(?string $comment): void;
    public abstract function getStartPosition(): int;
    public abstract function setStartPosition(int $start_position): void;
    public abstract function getEndPosition(): int;
    public abstract function setEndPosition(int $end_position): void;
}
