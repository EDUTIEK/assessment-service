<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\Assessment\Data\PdfFeedbackMode;
use Edutiek\AssessmentService\Assessment\Data\PdfSettings as PdfSettings;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImages;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\EssayTask\ImageProcessing\FullService as ImageProcssing;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as SystemHtmlProcessing;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\Task\CorrectorComment\CorrectorCommentInfo;
use Edutiek\AssessmentService\Task\CorrectorComment\InfoService as CommentsService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings as AssessmentSettings;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings as TaskSettings;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SINGLE_COMMENTS = 'single_comments';
    public const PART_MULTI_COMMENTS = 'multi_comments';

    private const KEY_CORRECTOR_1 = 'comments_corrector1';
    private const KEY_CORRECTOR_2 = 'comments_corrector2';
    private const KEY_CORRECTOR_3 = 'comments_corrector3';
    private const KEY_COMMENTS_ALL = 'comments_all';

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private PdfSettings $pdf_settings,
        private EssayImages $essay_images,
        private HtmlProcessing $html_processing,
        private ImageProcssing $image_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private FileStorage $file_storage,
        private FileStorage $temp_storage,
        private SystemHtmlProcessing $system_processing,
        private CommentsService $comments,
        private AssessmentSettings $assessment_settings,
        private CorrectorService $correctors,
        private TaskSettings $task_settings,
        private GradingProvider $gradings,
    ) {
    }

    public function getAvailableParts(): array
    {
        if (!$this->task_settings->getEnableComments()) {
            return [];
        }

        if ($this->assessment_settings->hasMultipleCorrectors()) {
            return [
                new PdfConfigPart(
                    "EssayTask",
                    self::KEY_CORRECTOR_1,
                    self::PART_SINGLE_COMMENTS,
                    $this->language->txt('pdf_part_comments_corrector1'),
                    true
                ),
                new PdfConfigPart(
                    "EssayTask",
                    self::KEY_CORRECTOR_2,
                    self::PART_SINGLE_COMMENTS,
                    $this->language->txt('pdf_part_comments_corrector2'),
                    true
                ),
                new PdfConfigPart(
                    "EssayTask",
                    self::KEY_CORRECTOR_3,
                    self::PART_SINGLE_COMMENTS,
                    $this->language->txt('pdf_part_comments_corrector3'),
                    true
                ),
                new PdfConfigPart(
                    "EssayTask",
                    self::KEY_COMMENTS_ALL,
                    self::PART_MULTI_COMMENTS,
                    $this->language->txt('pdf_part_comments_all_correctors'),
                    true
                ),
            ];
        } else {
            return [
                new PdfConfigPart(
                    "EssayTask",
                    self::KEY_COMMENTS_ALL,
                    self::PART_MULTI_COMMENTS,
                    $this->language->txt('pdf_part_comments'),
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

        if (!$this->task_settings->getEnableComments()) {
            return null;
        }

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

        $allowed_positions = [];
        $gradings = $this->gradings->gradingsForTaskAndWriter($task_id, $writer_id);
        $corrector = $this->correctors->oneByUserId($this->user_id);
        foreach ($positions as $position) {
            if (!empty($grading = $gradings[$position->value] ?? null)) {
                if ($grading->isAuthorized() || $grading->getCorrectorId() === $corrector->getId()) {
                    $allowed_positions[] = $position;
                }
            }
        }

        $infos = $this->comments->getInfos($task_id, $writer_id, $allowed_positions);
        $options = $options->withTitle($options->getTitle() . ' | ' . $this->getCorrectionTitle($key));

        if ($essay->hasPdfVersion()) {
            return $this->renderFromImages($key, $essay, $infos, $anonymous_corrector, $options);
        } else {
            return $this->renderFromText($key, $essay, $infos, $anonymous_corrector, $options);
        }
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     */
    private function renderFromText(string $key, Essay $essay, array $infos, bool $anonymous_corrector, Options $options): ?string
    {
        if ($this->pdf_settings->getFeedbackMode() == PdfFeedbackMode::SIDE_BY_SIDE) {
            $options = $options->withPortrait(false);
        }

        $data = [
            'partTitle' => $this->getCorrectionTitle($key),
            'partComments' => $this->html_processing->getCorrectedTextForPdf($essay, $infos)
        ];

        $html = $this->system_processing->fillTemplate(__DIR__ . '/templates/text_comments.html', $data);
        $html = $this->system_processing->addCorrectionStyles($html);

        return $this->pdf_processing->create($html, $options);
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     */
    private function renderFromImages(string $key, Essay $essay, array $infos, bool $anonymous_corrector, Options $options): ?string
    {
        $data = [
            'pages' => []
        ];

        $pdf_ids = [];
        $temp_ids = [];

        $start_page = $options->getStartPageNumber();
        foreach ($this->essay_images->getByEssayId($essay->getId()) as $page_no => $essay_image) {
            $page_no++; // stored page numbers are 1-based
            $stream = $this->file_storage->getFileStream(($essay_image->getFileId()));
            if ($stream !== null) {
                $raw_image = new ImageDescriptor(
                    $stream,
                    $essay_image->getWidth(),
                    $essay_image->getHeight(),
                    $essay_image->getMime()
                );

                $page_infos = $this->comments->filterAndLabelInfos($infos, $page_no);

                $applied_image = $this->image_processing->applyCommentsMarks(
                    $page_no,
                    $raw_image,
                    $page_infos
                );

                $file_info = $this->temp_storage->saveFile($applied_image->stream());
                $temp_ids[] = $image_id = $file_info->getId();

                if ($this->pdf_settings->getFeedbackMode() == PdfFeedbackMode::SIDE_BY_SIDE) {
                    // print image and comments beneath each other

                    $data['pages'][] = [
                        'partTitle' => $page_no == 1 ? $this->getCorrectionTitle($key) : '',
                        'pageBreakClass' => $page_no > 1 ? 'xlas-page-break' : '',
                        'src' => $this->temp_storage->getReadablePath($image_id),
                        'comments' => $this->html_processing->getCommentsHtml($page_infos),
                    ];
                } else {
                    // add comments to a separate page following the image

                    $html = $this->system_processing->fillTemplate(__DIR__ . '/templates/solo_image.html', [
                        'src' => $this->temp_storage->getReadablePath($image_id),
                    ]);
                    $pdf_ids[] = $id1 = $this->pdf_processing->create($html, $options->withStartPageNumber($start_page));
                    $start_page += $this->pdf_processing->count($id1);

                    $html = $this->system_processing->fillTemplate(__DIR__ . '/templates/solo_comments.html', [
                        'partTitle' => $this->getCorrectionTitle($key),
                        'partComments' => $this->html_processing->getCommentsHtml($page_infos),
                    ]);
                    $html = $this->system_processing->addCorrectionStyles($html);

                    if (!empty($page_infos)) {
                        $pdf_ids[] = $id2 = $this->pdf_processing->create($html, ($options->withStartPageNumber($start_page)));
                        $start_page += $this->pdf_processing->count($id2);
                    }
                }
            }
        }

        if ($this->pdf_settings->getFeedbackMode() == PdfFeedbackMode::SIDE_BY_SIDE) {
            $html = $this->system_processing->fillTemplate(__DIR__ . '/templates/image_comments.html', $data);
            $html = $this->system_processing->addCorrectionStyles($html);
            $pdf_id = $this->pdf_processing->create($html, $options->withPortrait(false));

        } else {
            $pdf_id = $this->pdf_processing->join($pdf_ids);
            $temp_ids = array_merge($temp_ids, $pdf_ids);
        }

        foreach ($temp_ids as $image_id) {
            $this->temp_storage->deleteFile($image_id);
        }

        return $pdf_id;
    }

    private function getCorrectionTitle($key)
    {
        return match($key) {
            self::KEY_CORRECTOR_1 => $this->language->txt('comments_corrector1'),
            self::KEY_CORRECTOR_2 => $this->language->txt('comments_corrector2'),
            self::KEY_CORRECTOR_3 => $this->language->txt('comments_corrector3'),
            default => $this->language->txt('comments_corrector_all'),
        };
    }
}
