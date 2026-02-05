<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\EssayTask\ImageProcessing\FullService as ImageProcssing;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as AssignmentsService;
use Edutiek\AssessmentService\Task\CorrectorComment\ReadService as CommentsService;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SINGLE_COMMENTS = 'single_comments';
    public const PART_MULTI_COMMENTS = 'multi_comments';

    private const KEY_CORRECTOR_1 = 'comments_corrector1';
    private const KEY_CORRECTOR_2 = 'comments_corrector2';
    private const KEY_CORRECTOR_3 = 'comments_corrector3';
    private const KEY_COMMENTS_ALL = 'comments_all';

    public function __construct(
        private Repositories $repos,
        private HtmlProcessing $html_processing,
        private ImageProcssing $image_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private CorrectionSettingsReadService $settings_service,
        private AssignmentsService $assignments,
        private CommentsService $comments,
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
        Options $options
    ): ?string {

        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if ($essay === null) {
            return null;
        }

        $comments = [];
        foreach ($this->assignments->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
            if ($key === self::KEY_COMMENTS_ALL
                || $key === self::KEY_CORRECTOR_1 && $assignment->getPosition()->value === 0
                || $key === self::KEY_CORRECTOR_2 && $assignment->getPosition()->value === 1
                || $key === self::KEY_CORRECTOR_3 && $assignment->getPosition()->value === 2
            ) {
                $comments = array_merge(
                    $comments,
                    $this->comments->allByIds(
                        $assignment->getTaskId(),
                        $assignment->getWriterId(),
                        $assignment->getCorrectorId()
                    )
                );
            }
        }

        if ($essay->hasPdfVersion()) {
            return $this->renderFromImages($essay, $comments, $anonymous_corrector, $options);
        } else {
            return $this->renderFromText($essay, $comments, $anonymous_corrector, $options);
        }
    }

    /**
     * @param CorrectorComment[] $comments
     */
    private function renderFromText(Essay $essay, array $comments, bool $anonymous_corrector, Options $options): ?string
    {
        $html = $this->html_processing->getCorrectedTextForPdf($essay, $comments);
        return $this->pdf_processing->create($html, $options);
    }

    /**
     * @param CorrectorComment[] $comments
     */
    private function renderFromImages(Essay $essay, array $comments, bool $anonymous_corrector, Options $options): ?string
    {
        return null;
    }


    private function getTitle(string $key, bool $anonymous_corrector): string
    {
        return '';
    }



}
