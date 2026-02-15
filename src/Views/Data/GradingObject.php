<?php

namespace Edutiek\AssessmentService\Views\Data;

use Edutiek\AssessmentService\Assessment\Data\Properties;
use Edutiek\AssessmentService\System\Data\UserData;

abstract class GradingObject
{
    abstract public function getAssessment(): Properties;
    abstract public function getUserData(): UserData;
    abstract public function getWriterId(): int;
    abstract public function isAttended(): bool;
    abstract public function isFinalized(): bool;
    abstract public function getPoints(): ?float;
    abstract public function getGrade(): ?string;
    abstract public function isPassed(): bool;
}