<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class RatingCriteria implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTitle(): string;
    public abstract function setTitle(string $title): void;
    public abstract function getDescription(): ?string;
    public abstract function setDescription(?string $description): void;
    public abstract function getPoints(): int;
    public abstract function setPoints(int $points): void;
    public abstract function getCorrectorId(): ?int;
    public abstract function setCorrectorId(?int $corrector_id): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
    public abstract function getGeneral(): int;
    public abstract function setGeneral(int $general): void;
}
