<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\PdfCreation;

use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as Writers;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as Correctors;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGrading;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings as AssessmentSettings;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings as TaskSettings;
use Edutiek\AssessmentService\Task\RatingCriterion\Factory as RatingCriterionServiceFactory;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as Assignments;
use Edutiek\AssessmentService\Task\CorrectorSummary\ReadService as Summaries;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\Data\HeadlineScheme;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SUMMARY = 'summary';
    public const PART_REVISION = 'revision';
    public const PART_CRITERIA = 'criteria';

    public const CORRECTOR_1 = 'corrector1';
    public const CORRECTOR_2 = 'corrector2';
    public const CORRECTOR_3 = 'corrector3';

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private AssessmentSettings $assessment_settings,
        private TaskSettings $task_settings,
        private RatingCriterionServiceFactory $criteria_services,
        private Assignments $assignments,
        private Summaries $summaries,
        private Writers $writers,
        private Correctors $correctors,
        private AssessmentGrading $grading,
        private Repositories $repos
    ) {
    }

    public function getAvailableParts(): array
    {
        $parts = [];
        foreach ([self::CORRECTOR_1, self::CORRECTOR_2, self::CORRECTOR_3] as $corrector) {
            foreach ([self::PART_CRITERIA, self::PART_SUMMARY, self::PART_REVISION] as $type) {
                if ($this->isPartAvailable($type, $corrector)) {
                    $parts[] = new PdfConfigPart(
                        "Task",
                        $type . '_' . $corrector,
                        $type,
                        $this->getPartTitle($type, $corrector),
                        true
                    );
                }
            }
        }

        return $parts;
    }

    private function isPartAvailable(string $type, string $corrector): bool
    {
        switch ($type) {
            case self::PART_CRITERIA:
                return $this->task_settings->getEnablePartialPoints() &&
                    $corrector === self::CORRECTOR_1 || $this->assessment_settings->hasMultipleCorrectors();
            case self::PART_SUMMARY:
                return $corrector === self::CORRECTOR_1 || $this->assessment_settings->hasMultipleCorrectors();
            case self::PART_REVISION:
                switch ($this->assessment_settings->getProcedure()) {
                    case CorrectionProcedure::NONE:
                        return false;
                    case CorrectionProcedure::APPROXIMATION:
                        // only corrector1 or 2 can approximate
                        return $this->assessment_settings->hasMultipleCorrectors() && $corrector !== self::CORRECTOR_3;
                    case CorrectionProcedure::CONSULTING:
                        // in consulting only corrector 2 can enter a revision text
                        return $this->assessment_settings->hasMultipleCorrectors() && $corrector === self::CORRECTOR_2;
                }
        }
        return false;
    }

    private function getPartTitle(string $type, string $corrector): string
    {
        if ($type == self::PART_REVISION) {
            $lang_var = match ($this->assessment_settings->getProcedure()) {
                CorrectionProcedure::APPROXIMATION => 'pdf_part_approximation_' . $corrector,
                CorrectionProcedure::CONSULTING => 'pdf_part_consulting',
                default => ''
            };
        } elseif ($this->assessment_settings->hasMultipleCorrectors()) {
            $lang_var = 'pdf_part_' . $type . '_' . $corrector;
        } else {
            $lang_var = 'pdf_part_' . $type;
        }

        return $this->language->txt($lang_var);
    }

    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        Options $options,
    ): ?string {

        [$type, $corrector] = explode('_', $key);
        if (!$this->isPartAvailable($type, $corrector)) {
            return null;
        }
        $part_title = $this->getPartTitle($type, $corrector);
        $options = $options->withTitle($options->getTitle() . ' | ' . $part_title);

        $position = match($corrector) {
            self::CORRECTOR_1 => GradingPosition::FIRST,
            self::CORRECTOR_2 => GradingPosition::SECOND,
            self::CORRECTOR_3 => GradingPosition::STITCH,
            default => null
        };

        $assignments = [];
        foreach ($this->assignments->allByTaskIdAndWriterId($task_id, $writer_id) as $ass) {
            $assignments[$ass->getPosition()->value] = $ass;
        }

        $assignment = $assignments[$position->value] ?? null;
        if ($assignment !== null) {
            $summary = $this->summaries->getForAssignment($assignment);
            $is_own = $this->correctors->oneById($assignment->getCorrectorId())?->getUserId() == $this->user_id;

            switch ($type) {
                case self::PART_CRITERIA:
                    return $this->renderCriteria($summary, $part_title, $is_own, $options);
                case self::PART_SUMMARY:
                    return $this->renderSummary($summary, $part_title, $is_own, $options);
                case self::PART_REVISION:
                    switch ($this->assessment_settings->getProcedure()) {
                        case CorrectionProcedure::APPROXIMATION:
                            return $this->renderApproximation($summary, $part_title, $options);
                        case CorrectionProcedure::CONSULTING:
                            $assignment1 = $assignments[GradingPosition::FIRST->value] ?? null;
                            if ($assignment1 !== null) {
                                $summary1 = $this->summaries->getForAssignment($assignment1);
                                $summary2 = $summary;
                                return $this->renderConsulting($summary1, $summary2, $part_title, $options);
                            }
                    }
            }
        }

        return null;
    }

    private function renderSummary(CorrectorSummary $summary, string $title, bool $is_own, Options $options): ?string
    {
        if (!$is_own && !$summary->isAuthorized()) {
            return null;
        }

        if ($summary->hasPdf()) {
            $pdf1 = $this->renderContent($title, $this->language->txt('pdf_summary_follows'), [], $options);
            $pdf2 = $this->renderContent(null, null, [$summary->getPoints()], $options);

            $joined = $this->pdf_processing->join([$pdf1, $summary->getSummaryPdf(), $pdf2]);
            $this->pdf_processing->cleanup([$pdf1, $pdf2]);
            return $joined;
        } else {
            $pdf = $this->renderContent(
                $title,
                $summary->getSummaryText(),
                [$summary->getPoints()],
                $options
            );
        }

        return $pdf;
    }

    private function renderApproximation(CorrectorSummary $summary, string $title, Options $options): ?string
    {
        $own_corrector_id = $this->correctors->oneByUserId($this->user_id)?->getId();
        $writer = $this->writers->oneByWriterId($summary->getWriterId());

        if ($summary->isRevised() || $writer?->isRevisionNeeded() && $summary->getCorrectorId() === $own_corrector_id) {

            return $this->renderContent(
                $title,
                $summary->getRevisionText(),
                [$summary->getRevisionPoints()],
                $options
            );
        }
        return null;
    }

    private function renderConsulting(CorrectorSummary $summary1, CorrectorSummary $summary2, string $title, Options $options): ?string
    {
        $own_corrector_id = $this->correctors->oneByUserId($this->user_id)?->getId();
        $writer = $this->writers->oneByWriterId($summary1->getWriterId());

        $show1 = $summary1->isRevised() || ($writer?->isRevisionNeeded() && $summary1->getCorrectorId() === $own_corrector_id);
        $show2 = $summary2->isRevised() || ($writer?->isRevisionNeeded() && $summary2->getCorrectorId() === $own_corrector_id);

        if ($show1 || $show2) {
            return $this->renderContent(
                $title,
                $show2 ? $summary2->getRevisionText() : null,
                [
                    GradingPosition::FIRST->value => $show1 ? $summary1->getPoints() : null,
                    GradingPosition::SECOND->value => $show2 ? $summary2->getPoints() : null,
                ],
                $options
            );
        }

        return null;
    }


    private function renderCriteria(CorrectorSummary $summary, string $title, bool $is_own, Options $options): ?string
    {
        if (!$is_own && !$summary->isAuthorized()) {
            return null;
        }

        // criterion_id => points
        $sum_of_points = [];

        // criterion id is 0 for points without a criterion
        foreach ($this->repos->correctorPoints()->allByTaskIdAndCorrectorId($summary->getTaskId(), $summary->getCorrectorId()) as $point) {
            $sum_of_points[(int) $point->getCriterionId()] = ($sum_of_points[$point->getCriterionId()] ?? 0) + $point->getPoints();
        }

        $criteria_service = $this->criteria_services->ratingCriterion($summary->getTaskId(), $this->ass_id, $this->user_id);

        $criteria_by_type = [
            'general' => [],
            'comment' => [
                $criteria_service->new()
                    ->setId(0)
                    ->setCorrectorId($summary->getCorrectorId())
                    ->setTaskId($summary->getTaskId())
                    ->setGeneral(0)
                    ->setTitle($this->language->txt('independent_points'))
                    ->setDescription($this->language->txt('independent_points_description'))
            ]
        ];

        foreach ($criteria_service->allForCorrector($summary->getCorrectorId()) as $criterion) {
            $criteria_by_type[$criterion->getGeneral() ? 'general' : 'comment'][] = $criterion;
        }

        $data = [
            'title' => $title,
            'head_title' => $this->language->txt('criterion_title'),
            'head_description' => $this->language->txt('criterion_description'),
            'head_max_points' => $this->language->txt('criterion_max_points'),
            'head_points' => $this->language->txt('criterion_points'),
            'tables' => [],
         ];

        foreach ($criteria_by_type as $type => $criteria) {
            if (!empty($criteria)) {
                $table = [
                    'title' => $this->language->txt($type . '_points'),
                    'rows' => []
                ];
                foreach ($criteria as $criterion) {
                    $table['rows'][] = [
                        'title' => $criterion->getTitle(),
                        'description' => $criterion->getDescription(),
                        'max_points' => $criterion->getPoints(),
                        'points' => $sum_of_points[$criterion->getId()] ?? 0
                    ];
                }
                $data['tables'][] = $table;
            }
        }

        $html = $this->html_processing->fillTemplate(__DIR__ . '/templates/criteria.html', $data);
        $html = $this->html_processing->addCorrectionStyles($html);
        return $this->pdf_processing->create($html, $options);
    }

    /**
     * @param array<float|null> $points
     */
    private function renderContent(?string $title, ?string $content, array $points, Options $options): ?string
    {
        $data_points = [];
        foreach ($points as $position => $value) {
            if ($value !== null) {
                $level = $this->grading->getGradLevelForPoints($value);
                $label = $this->language->txt(count($points) == 1 ? 'pdf_label_points' : 'pdf_label_points_pos' . $position);
                $data_points[] = [
                    'label_points' => $label,
                    'points' => $value,
                    'grade' => $level?->getGrade(),
                    'statement' => $level?->getStatement(),
                ];
            }
        }

        $data = [
            'title' => $title,
            'content' => $this->html_processing->addContentStyles(
                $this->html_processing->secureContent($content ?? ''),
                false,
                HeadlineScheme::THREE
            ),
            'points' => $data_points
        ];

        $html = $this->html_processing->fillTemplate(__DIR__ . '/templates/content.html', $data);
        $html = $this->html_processing->addCorrectionStyles($html);
        return $this->pdf_processing->create($html, $options);
    }
}
