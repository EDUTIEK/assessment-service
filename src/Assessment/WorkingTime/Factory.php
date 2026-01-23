<?php

namespace Edutiek\AssessmentService\Assessment\WorkingTime;

use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Factory
{
    public function __construct(
        private LanguageService $language
    ) {
    }

    public function workingTime(OrgaSettings $orga, ?IndividualWorkingTime $writer = null): FullService
    {
        return new Service(
            $this->language,
            $orga,
            $writer
        );
    }
}
