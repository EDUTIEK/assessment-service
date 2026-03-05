<?php

namespace Edutiek\AssessmentService\Task\CorrectorTaskPrefs;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use ILIAS\Plugin\LongEssayAssessment\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorTaskPrefs;

interface FullService
{
    public function save(CorrectorTaskPrefs $preferences): void;
    public function get(int $corrector_id, int $task_id): CorrectorTaskPrefs;
    public function getEnabledCriterionCopyCorrectorIds(int $task_id): array;
}
