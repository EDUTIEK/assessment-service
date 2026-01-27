<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;

readonly class WritingProvider implements PdfPartProvider
{
    public const PART_ESSAY = 'essay';

    public function __construct(
        private int $ass_id,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private Repositories $repos
    ) {
    }

    public function getAvailableParts(): array
    {
        return [
            new PdfConfigPart(
                "EssayTask",
                'essay',
                self::PART_ESSAY,
                $this->language->txt('pdf_part_essay'),
                true
            ),
        ];
    }

    public function renderPart(string $key, int $task_id, int $writer_id): ?string
    {
        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if ($essay) {
            return $this->renderEssay($essay);
        }
        return null;
    }

    public function renderEssay(Essay $essay): ?string
    {
        $created = null;
        if (!empty($essay->getWrittenText()) && !$essay->hasPdfFromWrittenText()) {
            $settings = $this->repos->writingSettings()->one($this->ass_id) ?? $this->repos->writingSettings()->new();
            $html = $this->html_processing->processWrittenText($essay, $settings, true);

            $options = new Options();
            if ($settings->getAddCorrectionMargin()) {
                $options = $options->withLeftMargin($options->getLeftMargin() + $settings->getLeftCorrectionMargin());
                $options = $options->withRightMargin($options->getRightMargin() + $settings->getRightCorrectionMargin());
            }
            $created = $this->pdf_processing->create($html, $options);
        }

        if ($created && $essay->getPdfVersion()) {
            $id = $this->pdf_processing->join([$created, $essay->getPdfVersion()]);
            $this->pdf_processing->cleanup([$created]);
            return $id;

        } elseif ($created) {
            return $created;

        } else {
            return $essay->getPdfVersion();
        }
    }
}
