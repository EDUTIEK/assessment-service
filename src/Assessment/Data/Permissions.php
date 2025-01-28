<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class Permissions implements AssessmentEntity
{
    public abstract function getAssessmentId(): int;
    public abstract function getContextId(): int;
    public abstract function getUserId(): int;


    public abstract function getMaintainTask(): bool;
    public abstract function getMaintainWriters(): bool;
    public abstract function getMaintainCorrectors(): bool;
}