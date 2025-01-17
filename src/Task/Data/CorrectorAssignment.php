<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorAssignment implements TaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getWriterId(): int;
    public abstract function setWriterId(int $writer_id): self;
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): self;
    public abstract function getPosition(): int;
    public abstract function setPosition(int $position): self;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): self;
}
