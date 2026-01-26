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
        private PdfProcessing $processor,
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

        $ids = [];
        if (!empty($essay->getWrittenText())) {
            $ids[] = $this->processor->create(
                (string) $essay->getWrittenText(),
                (new Options())
            );
        }
        if ($essay->getPdfVersion() !== null) {
            $ids[] = $essay->getPdfVersion();
        }

        if (count($ids) == 0) {
            return null;
        } elseif (count($ids) > 1) {
            return $this->processor->join($ids);
        } else {
            return reset($ids);
        }
    }
}
