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
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Properties\ReadService as PropetiesReadService;
use Edutiek\AssessmentService\Task\Manager\ReadService as TaskManagerReadService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;

readonly class WritingProvider implements PdfPartProvider
{
    public const PART_ESSAY = 'essay';

    public function __construct(
        private int $ass_id,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private Repositories $repos,
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

    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        Options $options,
    ): ?string {
        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if (!$essay) {
            return null;
        }

        if ($essay->hasPdfVersion() && (!$essay->hasWrittenText() || $essay->hasPdfFromWrittenText())) {
            // only the pdf file is relevant
            // return a copy to protect the original from cleanup
            return $this->pdf_processing->copy($essay->getPdfVersion());
        }

        if ($essay->hasPdfVersion() && $essay->hasWrittenText()) {
            // both pdf and written text are relevant - create text pdf and join it with the odf file
            $created = $this->renderWrittenText($essay, $anonymous_writer, $options);
            $id = $this->pdf_processing->join([$created, $essay->getPdfVersion()]);
            $this->pdf_processing->cleanup([$created]);
            return $id;
        }

        if ($essay->hasWrittenText()) {
            return $this->renderWrittenText($essay, $anonymous_writer, $options);
        }

        return null;
    }

    /**
     * Render the written text from an essay as a pdf file
     * This is internally public for:
     *  - essay service to convert a text to a pdf file
     *  - essayImage service to create page images for an essay
     */
    public function renderWrittenText(
        Essay $essay,
        bool $anonymous_writer,
        Options $options
    ): string {

        $settings = $this->repos->writingSettings()->one($this->ass_id) ?? $this->repos->writingSettings()->new();
        $html = $this->html_processing->getWrittenTextForPdf($essay, $settings);

        if ($settings->getAddCorrectionMargin()) {
            $options = $options->withLeftMargin($options->getLeftMargin() + $settings->getLeftCorrectionMargin());
            $options = $options->withRightMargin($options->getRightMargin() + $settings->getRightCorrectionMargin());
        }

        return $this->pdf_processing->create($html, $options);
    }
}
