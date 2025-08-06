<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as CorrectorAssignmentsFullService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\Service as CorrectorAssignmentsService;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    /**
     * Translation of language variables
     */
    public function language(string $code) : LanguageService
    {
        return $this->instances[LanguageService::class][$code] ??= $this->dependencies->systemApi()->language()
            ->addLanguage('de', require(__DIR__ . '/../Languages/de.php'))
            ->setLanguage($code);
    }


    public function correctorAssignments(int $ass_id, int $user_id) : CorrectorAssignmentsFullService
    {
        return $this->instances[CorrectorAssignmentsService::class][$ass_id] ??= new CorrectorAssignmentsService(
            $ass_id,
            $this->dependencies->assessmentApis($ass_id, $user_id)->correctionSettings()->get(),
            $this->dependencies->assessmentApis($ass_id, $user_id)->writer(),
            $this->dependencies->repositories()
        );
    }
}