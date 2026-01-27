<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_FRONTPAGE = 'frontpage';

    public function __construct(
        private int $ass_id,
        private PdfProcessing $processing,
        private LanguageService $language
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

    public function renderPart(string $key, int $task_id, int $writer_id): ?string
    {
        // todo: fill with content for a task and writer
        return null;
    }
}
