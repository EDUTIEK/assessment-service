<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\CorrectionSettings;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettingsError;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Pseudonym\FullService as PseudonymService;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private PseudonymService $pseudonym_service,
        private LanguageService $language,
    ) {
    }

    public function get(): CorrectionSettings
    {
        $setting = $this->repos->correctionSettings()->one($this->ass_id) ??
            $this->repos->correctionSettings()->new()->setAssId($this->ass_id);

        // return a clone to allow a comparison with previous values in save()
        return clone $setting;
    }

    public function validate(CorrectionSettings $settings): Result
    {
        $this->checkScope($settings);
        $result = new Result();

        if ($settings->getRequiredCorrectors() === 1
            && ($settings->getProcedureWhenDecimals() || $settings->getProcedureWhenDistance())) {
            $result->addFailure($this->language->txt('correction_procedure_needs_two_correctors'));
        }

        return $result;
    }

    public function save(CorrectionSettings $settings): void
    {
        $this->checkScope($settings);

        $existing = $this->repos->correctionSettings()->one($this->ass_id);
        $this->repos->correctionSettings()->save($settings);
        if ($settings->getPseudonymization() !== $existing?->getPseudonymization()) {
            $this->pseudonym_service->changeForAll($settings->getPseudonymization());
        }
    }

    private function checkScope(CorrectionSettings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
