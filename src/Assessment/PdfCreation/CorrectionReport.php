<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Data\HeadlineScheme;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;

readonly class CorrectionReport
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $lang
    ) {
    }

    public function render(Options $options): string
    {
        $data = $this->getData();
        $html = $this->html_processing->fillTemplate(__DIR__ . '/templates/report.html', $data);
        $html = $this->html_processing->addContentStyles($html, false, HeadlineScheme::THREE);
        $html = $this->html_processing->addCorrectionStyles($html);
        return $this->pdf_processing->create($html, $options);
    }

    private function getData(): array
    {
        $data = [];
        $i = 1;
        foreach ($this->repos->corrector()->allByAssId($this->ass_id) as $corrector) {
            if (!empty($corrector->getCorrectionReport())) {
                $data['correctors'][] = [
                    'title' => $this->lang->txt('corrector') . ' ' . $i++,
                    'report' => $corrector->getCorrectionReport()
                ];
            }
        }
        return $data;
    }
}
