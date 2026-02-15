<?php

namespace Edutiek\AssessmentService\Views\Data;

use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\Assessment\Data\Properties;

abstract class StatisticView
{
    abstract public function getCount(): int;
    abstract public function getPassed(): int;
    abstract public function getNotPassed(): int;
    abstract public function getAttended(): int;
    abstract public function getNotAttended(): int;
    abstract public function getAveragePoints(): ?float;
    abstract public function getNotPassedQuota(): ?float;
    abstract public function getUser(): ?UserData;
    abstract public function getTitle(): string;
    abstract public function getGradingObjects(): ?array;
    abstract public function getGradeCounts(): array;
    abstract public function getPointsCounts(): array;
    abstract public function isMaxPointUniform(): bool;
    abstract public function isGradesUniform(): bool;
    /**
     * @return UserData[]
     */
    abstract public function getUsers(): array;

    /**
     * @return Properties[]
     */
    abstract public function getAssessments(): array;
    abstract public function fromUser(UserData $user): self;
    abstract public function fromAssessent(Properties $assessment): self;
    abstract public function fromUserAndAssessemnt(UserData $user, Properties $assessment): self;
}
