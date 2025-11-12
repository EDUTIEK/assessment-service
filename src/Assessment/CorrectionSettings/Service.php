<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\CorrectionSettings;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettingsError;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function get(): CorrectionSettings
    {
        return $this->repos->correctionSettings()->one($this->ass_id) ??
            $this->repos->correctionSettings()->new()->setAssId($this->ass_id);
    }

    public function validate(CorrectionSettings $settings): bool
    {
        $this->checkScope($settings);

        if ($settings->getRequiredCorrectors() === 1 && ($settings->getProcedureWhenDecimals() || $settings->getProcedureWhenDistance())) {
            $settings->addValidationError(CorrectionSettingsError::ATLEAST_TWO_CORRECTORS);
        }

        return empty($settings->getValidationErrors());
    }

    public function save(CorrectionSettings $settings): void
    {
        $this->checkScope($settings);
        $this->repos->correctionSettings()->save($settings);
    }

    private function checkScope(CorrectionSettings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
