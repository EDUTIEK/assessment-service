<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
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
        return match($key) {
            default => $this->renderEssay($task_id, $writer_id),
        };
    }

    private function renderEssay(int $task_id, int $writer_id): ?string
    {
        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if ($essay === null) {
            return null;
        }

        if ($essay->getPdfVersion() !== null) {
            // todo: join with text, if needed
            return $essay->getPdfVersion();
        } else {
            // todo: get options from pdf settings
            return $this->pdf_processing->create(
                (string) $essay->getWrittenText(),
                (new Options())
            );
        }
    }
}
