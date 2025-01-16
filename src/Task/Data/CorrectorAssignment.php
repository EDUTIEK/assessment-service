<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorAssignment implements TaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getWriterId(): int;
    public abstract function setWriterId(int $writer_id): void;
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): void;
    public abstract function getPosition(): int;
    public abstract function setPosition(int $position): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
}
