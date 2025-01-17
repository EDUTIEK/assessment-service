<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class GradeLevel implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getMinPoints(): float;
    public abstract function setMinPoints(float $min_points): self;
    public abstract function getGrade(): string;
    public abstract function setGrade(string $grade): self;
    public abstract function getCode(): ?string;
    public abstract function setCode(?string $code): self;
    public abstract function getPassed(): bool;
    public abstract function setPassed(bool $passed): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
}
