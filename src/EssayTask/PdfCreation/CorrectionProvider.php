<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\EssayTask\ImageProcessing\FullService as ImageProcssing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\Task\CorrectorComment\CorrectorCommentInfo;
use Edutiek\AssessmentService\Task\CorrectorComment\InfoService as CommentsService;

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

        $positions = match($key) {
            self::KEY_COMMENTS_ALL => [GradingPosition::FIRST, GradingPosition::SECOND, GradingPosition::STITCH],
            self::KEY_CORRECTOR_1 => [GradingPosition::FIRST],
            self::KEY_CORRECTOR_2 => [GradingPosition::SECOND],
            self::KEY_CORRECTOR_3 => [GradingPosition::STITCH],
        };

        $infos = $this->comments->getInfos($task_id, $writer_id, $positions);

        if ($essay->hasPdfVersion()) {
            return $this->renderFromImages($essay, $infos, $anonymous_corrector, $options);
        } else {
            return $this->renderFromText($essay, $infos, $anonymous_corrector, $options);
        }
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     */
    private function renderFromText(Essay $essay, array $infos, bool $anonymous_corrector, Options $options): ?string
    {
        $html = $this->html_processing->getCorrectedTextForPdf($essay, $infos);
        return $this->pdf_processing->create($html, $options);
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     */
    private function renderFromImages(Essay $essay, array $infos, bool $anonymous_corrector, Options $options): ?string
    {
        return null;
    }
}
