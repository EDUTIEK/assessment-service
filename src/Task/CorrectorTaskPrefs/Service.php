<?php

namespace Edutiek\AssessmentService\Task\CorrectorTaskPrefs;

use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as WholeProcessService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\LogEntry\MentionUser;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\LogEntry\Type as LogEntryType;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\CorrectorSummary\ReadService as SummaryService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\CorrectorTaskPrefs;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;

readonly class Service implements FullService
{
    public function __construct(
        private Repositories $repos,
    ) {
    }

    public function save(CorrectorTaskPrefs $preferences): void
    {
        $this->repos->correctorTaskPrefs()->save($preferences);
    }

    public function get(int $corrector_id, int $task_id): CorrectorTaskPrefs
    {
        return $this->repos->correctorTaskPrefs()->oneByCorrectorIdAndTaskId($corrector_id, $task_id) ?? $this->repos->correctorTaskPrefs()->new()->setCorrectorId($corrector_id)->setTaskId($task_id);
    }

    public function getEnabledCriterionCopyCorrectorIds(int $task_id): array
    {
        return array_map(fn(CorrectorTaskPrefs $prefs) => $prefs->getCorrectorId(),
            array_filter(
                $this->repos->correctorTaskPrefs()->allByTaskId($task_id),
                fn(CorrectorTaskPrefs $prefs) => $prefs->getCriterionCopy()
            )
        );
    }
}
