<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class GradeLevel implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getMinPoints(): float;
    abstract public function setMinPoints(float $min_points): self;
    abstract public function getGrade(): string;
    abstract public function setGrade(string $grade): self;
    abstract public function getCode(): ?string;
    abstract public function setCode(?string $code): self;
    abstract public function getPassed(): bool;
    abstract public function setPassed(bool $passed): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getStatement() : ?string;
    abstract public function setStatement(?string $statement) : self;
}
