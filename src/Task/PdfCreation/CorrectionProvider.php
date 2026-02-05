<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\PdfCreation;

use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SUMMARY = 'summary';
    public const PART_REVISION = 'revision';
    public const PART_CRITERIA = 'criteria';

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

        $parts = [];
        if ($settings->hasMultipleCorrectors()) {
            foreach ([self::PART_SUMMARY, self::PART_REVISION, self::PART_CRITERIA] as $type) {
                foreach (['corrector1', 'corrector2', 'corrector3'] as $corrector) {
                    $key = $type . '_' . $corrector;
                    $parts[$key] = new PdfConfigPart(
                        "Task",
                        $key,
                        $type,
                        $this->language->txt('pdf_part_' . $key),
                        true
                    );
                }
            }
            // stich decision has no revision
            unset($parts[self::PART_REVISION . '_corrector3']);

        } else {
            foreach ([self::PART_SUMMARY, self::PART_CRITERIA] as $type) {
                $parts[] = new PdfConfigPart(
                    "Task",
                    $type . '_corrector1',
                    $type,
                    $this->language->txt('pdf_part_' . $type),
                    true
                );
            }
        }

        return array_values($parts);
    }


    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        Options $options,
    ): ?string {
        // todo: fill with content for a task and writer
        return null;
    }
}
