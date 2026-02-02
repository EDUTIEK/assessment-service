<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SINGLE_COMMENTS = 'single_comments';
    public const PART_MULTI_COMMENTS = 'multi_comments';

    public function __construct(
        private int $ass_id,
        private PdfProcessing $processing,
        private LanguageService $language,
        private CorrectionSettingsReadService $settings_service,
    ) {
    }

    public function getAvailableParts(): array
    {
        $settings = $this->settings_service->get();

        if ($settings->hasMultipleCorrectors()) {
            return [
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
                    $this->language->txt('pdf_part_comments_corrector3'),
                    true
                ),
                new PdfConfigPart(
                    "EssayTask",
                    'comments_all',
                    self::PART_MULTI_COMMENTS,
                    $this->language->txt('pdf_part_comments_all_correctors'),
                    true
                ),
            ];
        } else {
            return [
                new PdfConfigPart(
                    "EssayTask",
                    'comments_all',
                    self::PART_MULTI_COMMENTS,
                    $this->language->txt('pdf_part_comments_all_correctors'),
                    true
                ),
            ];

        }
    }

    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        bool $with_header,
        bool $with_footer
    ): ?string {
        // todo: fill with content for a task and writer
        return null;
    }
}
