<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\PdfFormat;
use Edutiek\AssessmentService\Assessment\Data\PdfSettings;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\PdfCreator\Options;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_FRONTPAGE = 'frontpage';

    public function __construct(
        private int $ass_id,
        private PdfSettings $pdf_settings,
        private CorrectionSettings $correction_settings,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
    ) {
    }

    public function getAvailableParts(): array
    {
        return [
            new PdfConfigPart(
                "Assessment",
                self::PART_FRONTPAGE,
                self::PART_FRONTPAGE,
                $this->language->txt('pdf_part_frontpage'),
                false
            ),
        ];
    }

    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        Options $options,
    ): ?string {

        $data = $this->getData($task_id, $writer_id);

        switch ($this->pdf_settings->getFormat()) {
            case PdfFormat::BY:
                $html = $this->fillTemplate($data, 'frontpage_by.html', 'frontpage_by.css');
                return $this->pdf_processing->create($html, (new Options())
                    ->withLeftMargin(40)
                    ->withRightMargin(40)
                    ->withTopMargin(50)
                    ->withPrintHeader(false)
                    ->withPrintFooter(false));
                break;
        }
        return null;
    }

    private function getData(int $task_id, int $writer_id): array
    {
        return [];
    }

    private function fillTemplate(array $data, string $template_file, ?string $style_file = null): string
    {
        $html = $this->html_processing->fillTemplate(__DIR__ . '/templates/' . $template_file, $data);
        if ($style_file) {
            $style = file_get_contents(__DIR__ . '/templates/' . $style_file);
            $html = "<style>\n$style\n</style>\n$html";
        }
        return $html;
    }
}
