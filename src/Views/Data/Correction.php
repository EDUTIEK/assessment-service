<?php

namespace Edutiek\AssessmentService\Views\Data;

use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\System\Data\UserData;

abstract class Correction
{
    abstract public function getCorrectorAssignment(): CorrectorAssignment;
    abstract public function getCorrectorSummary(): ?CorrectorSummary;
    abstract public function getCorrector(): Corrector;
    abstract public function getCorrectorData(): UserData;
}