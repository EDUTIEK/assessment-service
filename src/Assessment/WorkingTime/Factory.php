<?php

namespace Edutiek\AssessmentService\Assessment\WorkingTime;

use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\WorkingTime\FullService as FullWorkingTime;
use Edutiek\AssessmentService\Assessment\WorkingTime\Service as WorkingTime;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Factory
{
    public function __construct(
        private LanguageService $language
    ) {
    }

    public function workingTime(OrgaSettings $orga, Writer|IndividualWorkingTime|null $writer = null): FullWorkingTime
    {
        return new WorkingTime(
            $this->language,
            $orga,
            $writer
        );
    }
}
