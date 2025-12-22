<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\ValidationError;
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

    public function validate(OrgaSettings $settings) : bool
    {
        $this->checkScope($settings);

        $valid = true;
        $working_time = $this->working_time_factory->workingTime($settings);
        if (!$working_time->validate($settings)) {
            $valid = false;
        }

        if ($settings->getCorrectionStart() !== null && $settings->getCorrectionEnd() !== null
            && $settings->getCorrectionStart() > $settings->getCorrectionEnd()) {
            $settings->addValidationError(ValidationError::CORRECTION_END_BEFORE_CORRECTION_START);
            $valid = false;
        }

        if ($settings->getReviewStart() !== null && $settings->getReviewEnd() !== null
            && $settings->getReviewStart() > $settings->getReviewEnd()) {
            $settings->addValidationError(ValidationError::REVIEW_END_BEFORE_REVIEW_START);
            $valid = false;
        }

        return $valid;
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
