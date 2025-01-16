<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class GradeLevel implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getMinPoints(): float;
    public abstract function setMinPoints(float $min_points): void;
    public abstract function getGrade(): string;
    public abstract function setGrade(string $grade): void;
    public abstract function getCode(): ?string;
    public abstract function setCode(?string $code): void;
    public abstract function getPassed(): bool;
    public abstract function setPassed(bool $passed): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
}
