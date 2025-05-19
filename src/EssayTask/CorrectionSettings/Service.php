<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\CorrectionSettings;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\CorrectionSettings\FullService;
use Edutiek\AssessmentService\EssayTask\Data\CorrectionSettings;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function get() : CorrectionSettings
    {
        return $this->repos->correctionSettings()->one($this->ass_id) ??
            $this->repos->correctionSettings()->new()->setAssId($this->ass_id);
    }

    public function save(CorrectionSettings $settings) : void
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