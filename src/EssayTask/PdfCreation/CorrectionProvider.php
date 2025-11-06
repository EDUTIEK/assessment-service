<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_CRITERIA = 'criteria';
    public const PART_SINGLE_COMMENTS = 'single_comments';
    public const PART_MULTI_COMMENTS = 'multi_comments';

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
                "EssayTask",
                'criteria_corrector1',
                self::PART_CRITERIA,
                $this->language->txt('pdf_part_criteria_corrector1'),
                true
            ),
            new PdfConfigPart(
                "EssayTask",
                'criteria_corrector2',
                self::PART_CRITERIA,
                $this->language->txt('pdf_part_criteria_corrector2'),
                true
            ),
            new PdfConfigPart(
                "EssayTask",
                'comments_corrector1',
                self::PART_SINGLE_COMMENTS,
                $this->language->txt('pdf_part_comments_corrector1'),
                true
            ),
            new PdfConfigPart(
                "EssayTask",
                'comments_corrector2',
                self::PART_SINGLE_COMMENTS,
                $this->language->txt('pdf_part_comments_corrector2'),
                true
            ),
            new PdfConfigPart(
                "EssayTask",
                'comments_corrector2',
                self::PART_SINGLE_COMMENTS,
                $this->language->txt('pdf_part_comments_all_correctors'),
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
