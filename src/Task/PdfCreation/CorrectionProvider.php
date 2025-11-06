<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\PdfCreation;

use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SUMMARY = 'summary';

    public function __construct(
        private int $ass_id,
        private PdfProcessing $processing,
        private LanguageService $language
    ) {
    }

    public function getAvailableParts(): array
    {
        // todo: check if assessment has multiple correctors per task
        return [
            new PdfConfigPart(
                "Task",
                'summary_corrector1',
                self::PART_SUMMARY,
                $this->language->txt('pdf_part_summary_corrector1'),
                true
            ),
            new PdfConfigPart(
                "Task",
                'summary_corrector2',
                self::PART_SUMMARY,
                $this->language->txt('pdf_part_summary_corrector2'),
                true
            ),
        ];
    }

    public function renderPart(string $key, int $task_id, int $writer_id): string
    {
        // todo: fill with content for a task and writer
        return $this->processing->create([], []);
    }
}
