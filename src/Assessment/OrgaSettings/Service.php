<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettingsError;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\WorkingTime;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WorkingTimeFactory $working_time_factory
    ) {
    }

    public function get(): OrgaSettings
    {
        return $this->repos->orgaSettings()->one($this->ass_id) ??
            $this->repos->orgaSettings()->new()->setAssId($this->ass_id);
    }

    public function validate(OrgaSettings $settings): bool
    {
        $this->checkScope($settings);
        $working_time = $this->working_time_factory->workingTime($settings);
        if ($working_time->isEndBeforeStart()) {
            $settings->addValidationError(OrgaSettingsError::LATEST_END_BEFORE_EARLIEST_START);
        }
        if ($working_time->isTimeLimitTooLong()) {
            $settings->addValidationError(OrgaSettingsError::TIME_LIMIT_TOO_LONG);
        }
        if ($settings->getSolutionAvailable()
            && $settings->getSolutionAvailableDate() !== null && $working_time->getWorkingDeadline() !== null
            && $settings->getSolutionAvailableDate() <= $working_time->getWorkingDeadline()) {
            $settings->addValidationError(OrgaSettingsError::TIME_EXCEEDS_SOLUTION_AVAILABILITY);
        }
        return empty($settings->getValidationErrors());
    }

    public function save(OrgaSettings $settings): void
    {
        $this->checkScope($settings);
        $this->repos->orgaSettings()->save($settings);
    }

    private function checkScope(OrgaSettings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
