<?php

namespace Edutiek\AssessmentService\Views\Data;

abstract class GradingObject
{
    abstract public function getReference(): int;
    abstract public function getWriterId(): int;
    abstract public function isAttended(): bool;
    abstract public function isFinalized(): bool;
    abstract public function getPoints(): ?int;
    abstract public function getGrade(): ?string;
    abstract public function isPassed(): bool;
}