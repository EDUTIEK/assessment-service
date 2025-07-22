<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\PdfSettings;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\PdfSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

readonly class Service implements FullService
{

    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function get() : PdfSettings
    {
        return $this->repos->pdfSettings()->one($this->ass_id) ??
            $this->repos->pdfSettings()->new()->setAssId($this->ass_id);
    }


    public function save(PdfSettings $settings) : void
    {
        $this->checkScope($settings);
        $this->repos->pdfSettings()->save($settings);
    }
    
    private function checkScope(PdfSettings $settings) 
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}