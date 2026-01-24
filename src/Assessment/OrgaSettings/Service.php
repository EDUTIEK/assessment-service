<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private WorkingTimeFactory $working_time_factory,
        private LanguageService $language
    ) {
    }

    public function get(): OrgaSettings
    {
        return $this->repos->orgaSettings()->one($this->ass_id) ??
            $this->repos->orgaSettings()->new()->setAssId($this->ass_id);
    }

    public function validate(OrgaSettings $settings) : Result
    {
        $this->checkScope($settings);
        $result = new Result();

        $working_time = $this->working_time_factory->workingTime($settings);
        $working_time->validate($result);

        if ($settings->getCorrectionStart() !== null && $settings->getCorrectionEnd() !== null
            && $settings->getCorrectionStart() > $settings->getCorrectionEnd()) {
            $result->addFailure($this->language->txt('orga_correction_end_before_correction_start'));
        }

        if ($settings->getReviewStart() !== null && $settings->getReviewEnd() !== null
            && $settings->getReviewStart() > $settings->getReviewEnd()) {
            $result->addFailure($this->language->txt('orga_review_end_before_review_start'));
        }

        return $result;
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
