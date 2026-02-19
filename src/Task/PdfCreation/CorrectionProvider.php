<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\PdfCreation;

use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as Correctors;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as Assignments;
use Edutiek\AssessmentService\Task\CorrectorSummary\ReadService as Summaries;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\System\Data\HeadlineScheme;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_SUMMARY = 'summary';
    public const PART_REVISION = 'revision';
    public const PART_CRITERIA = 'criteria';

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private CorrectionSettingsReadService $settings_service,
        private Assignments $assignments,
        private Summaries $summaries,
        private Correctors $correctors
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

            switch ($settings->getProcedure()) {
                case CorrectionProcedure::NONE:
                    unset($parts[self::PART_REVISION . '_corrector1']);
                    unset($parts[self::PART_REVISION . '_corrector2']);
                    break;
                case CorrectionProcedure::CONSULTING:
                    // in consulting only corrector 2 can enter a revision text
                    unset($parts[self::PART_REVISION . '_corrector1']);
                    break;
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
        $settings = $this->settings_service->get();

        [$type, $corr] = explode('_', $key);
        switch ($type) {
            case self::PART_CRITERIA:
                $part_title = $this->language->txt($settings->hasMultipleCorrectors() ?
                    'criteria_' . $corr : 'criteria');
                break;
            case self::PART_SUMMARY:
                $part_title = $this->language->txt($settings->hasMultipleCorrectors() ?
                'summary_' . $corr : 'summary');
                break;
            case self::PART_REVISION:
                if ($settings->getProcedure() == CorrectionProcedure::APPROXIMATION) {
                    $part_title = $this->language->txt('approximation_' . $corr);
                } else {
                    $part_title = $this->language->txt('consulting');
                }
                break;

        }

        $options = $options->withTitle($options->getTitle() . ' | ' . $part_title);

        $position = match($corr) {
            'corrector1' => GradingPosition::FIRST,
            'corrector2' => GradingPosition::SECOND,
            'corrector3' => GradingPosition::STITCH,
            'default' => null
        };

        $assignment = null;
        foreach ($this->assignments->allByTaskIdAndWriterId($task_id, $writer_id) as $ass) {
            if ($ass->getPosition() === $position) {
                $assignment = $ass;
            }
        }

        if ($assignment !== null) {
            $corrector = $this->correctors->oneById($assignment->getCorrectorId());
            $summary = $this->summaries->getForAssignment($assignment);
            $is_own = $corrector->getUserId() == $this->user_id;

            switch ($type) {
                case self::PART_CRITERIA:
                    return $this->renderCriteria($assignment, $part_title, $is_own, $options);
                case self::PART_SUMMARY:
                    return $this->renderSummary($summary, $part_title, $is_own, $options);
                case self::PART_REVISION:
                    return $this->renderRevision($summary, $part_title, $is_own, $options);
            }
        }

        return null;
    }

    private function renderSummary(CorrectorSummary $summary, string $title, bool $is_own, Options $options): ?string
    {
        if (!$is_own && !$summary->isAuthorized()) {
            return $this->renderContent($title, $this->language->txt('correction_not_authorized'), $options);
        } elseif (!empty($summary->getSummaryPdf())) {
            return $this->pdf_processing->copy($summary->getSummaryPdf());
        } else {
            return $this->renderContent($title, $summary->getSummaryText(), $options);
        }
    }

    private function renderRevision(CorrectorSummary $summary, string $title, bool $is_own, Options $options): ?string
    {
        if (!$is_own && !$summary->isRevised()) {
            return $this->renderContent($title, $this->language->txt('correction_not_revised'), $options);
        } else {
            return $this->renderContent($title, $summary->getRevisionText(), $options);
        }
    }

    private function renderCriteria(CorrectorAssignment $assignment, string $title, bool $is_own, Options $options): ?string
    {
        return null;
    }

    private function renderContent(string $title, string $content, Options $options): ?string
    {
        $html = $this->html_processing->fillTemplate(__DIR__ . '/templates/content.html', [
            'title' => $title,
            'content' => $this->html_processing->addContentStyles(
                $content,
                false,
                HeadlineScheme::THREE
            )
        ]);
        $html = $this->html_processing->addCorrectionStyles($html);
        return $this->pdf_processing->create($html, $options);
    }
}
