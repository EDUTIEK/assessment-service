<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectionItems;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Format\FullService as AssessmentFormatService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGradingService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\Task\CorrectionProcess\Service as CorrectionProcessService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as AssignmentsService;
use Edutiek\AssessmentService\Task\Format\FullService as FormatService;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectionItem;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSort;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;

readonly class Service implements FullService
{
    public function __construct(
        private int $user_id,
        private AssignmentsService $assignments,
        private CorrectionProcessService $process,
        private FormatService $format,
        private Repositories $repos,
        private CorrectionSettings $settings,
        private AssessmentFormatService $assessment_format,
        private AssessmentGradingService $assessment_grading,
        private CorrectorService $correctors,
        private WriterService $writers,
        private LanguageService $lang,
        private UserService $users,
    ) {
    }

    /**
     * @return CorrectionItem[]
     */
    public function getItems(bool $filtered, ?array $assignment_ids = null): array
    {
        $corrector = $this->correctors->oneByUserId($this->user_id);
        $assignments = $this->assignments->allByCorrectorId($corrector?->getId(), true);
        $filter = $this->getFilterValues();

        $items = [];
        foreach ($assignments as $assignment) {
            if ($assignment_ids !== null) {
                if (!in_array($assignment->getId(), $assignment_ids)) {
                    continue;
                }
            }

            if (isset($filter['position'])) {
                $value = $assignment->getPosition()->value ?? GradingPosition::FIRST->value;
                if (!in_array($value, (array) $filter['position'])) {
                    continue;
                }
            }

            $item = $this->buildCorrectionItem($assignment);

            if (isset($filter['grading_status'])) {
                $value = $item->getSummary()?->getGradingStatus()?->value ?? GradingStatus::NOT_STARTED->value;
                if (!in_array($value, (array) $filter['grading_status'])) {
                    continue;
                }
            }
            if (isset($filter['other_status'])) {
                $value = $item->getOtherSummary()?->getGradingStatus()?->lightStatus()?->value ?? GradingStatus::OPEN->value;
                if (!in_array($value, (array) $filter['other_status'])) {
                    continue;
                }
            }
            if (isset($filter['correction_status'])) {
                $value = $item->getWriter()?->getCorrectionStatus()?->value ?? CorrectionStatus::OPEN->value;
                if (!in_array($value, (array) $filter['correction_status'])) {
                    continue;
                }
            }

            $items[] = $item;
        }

        if ($this->settings->getStartpageFixed()) {
            usort($items, $this->sortItems(...));
        }
        return $items;
    }

    public function buildCorrectionItem(CorrectorAssignment $assignment): CorrectionItem
    {
        $writer = $this->writers->oneByWriterId($assignment->getWriterId());
        $task_title = $this->repos->settings()->one($assignment->getTaskId())->getTitle();
        $co_assignment = $co_user_data = $co_summary = $own_summary = null;

        if ($this->settings->getRequiredCorrectors() > 1 && $assignment->getPosition() !== GradingPosition::STITCH) {

            foreach ($this->repos->correctorAssignment()->allByWriterId($assignment->getWriterId()) as $a) {
                if ($a->getWriterId() === $assignment->getWriterId()
                    && $a->getTaskId() === $assignment->getTaskId()
                    && $a->getCorrectorId() !== $assignment->getCorrectorId()) {
                    $co_assignment = $a;
                }
            }

            if ($co_assignment !== null) {
                $co_corrector = $this->correctors->oneById($co_assignment->getCorrectorId());
                $co_user_data = $this->users->getUser($co_corrector?->getUserId() ?? -1);
            }
        }

        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterId(
            $assignment->getTaskId(),
            $assignment->getWriterId()
        ) as $summary) {
            if ($summary->getCorrectorId() === $assignment->getCorrectorId()) {
                $own_summary = $summary;
            }
            if ($summary->getCorrectorId() === $co_assignment?->getCorrectorId()) {
                $co_summary = $summary;
            }
        }

        return new CorrectionItem(
            $assignment->getId(),
            $writer,
            $assignment,
            $own_summary,
            $task_title,
            $co_summary,
            $co_user_data,
            $co_assignment
        );
    }

    public function getFilterOptions(): array
    {
        $options = [];

        $options['position'] = [
            (string) GradingPosition::FIRST->value => $this->lang->txt('grading_pos_first'),
            (string) GradingPosition::SECOND->value => $this->lang->txt('grading_pos_second'),
            (string) GradingPosition::STITCH->value => $this->lang->txt('grading_pos_stitch'),
        ];

        $options['grading_status'] = [
            GradingStatus::NOT_STARTED->value => $this->lang->txt('grading_not_started'),
            GradingStatus::OPEN->value => $this->lang->txt('grading_open'),
            GradingStatus::PRE_GRADED->value => $this->lang->txt('grading_pre_graded'),
            GradingStatus::AUTHORIZED->value => $this->lang->txt('grading_authorized'),
            GradingStatus::REVISED->value => match($this->settings->getProcedure()) {
                CorrectionProcedure::APPROXIMATION => $this->lang->txt('grading_approximated'),
                CorrectionProcedure::CONSULTING => $this->lang->txt('grading_consulted'),
                CorrectionProcedure::NONE => $this->lang->txt('grading_revised'),
            },
        ];

        $options['other_status'] = [
            GradingStatus::OPEN->value => $this->lang->txt('grading_open'),
            GradingStatus::AUTHORIZED->value => $this->lang->txt('grading_authorized'),
            GradingStatus::REVISED->value => match($this->settings->getProcedure()) {
                CorrectionProcedure::APPROXIMATION => $this->lang->txt('grading_approximated'),
                CorrectionProcedure::CONSULTING => $this->lang->txt('grading_consulted'),
                CorrectionProcedure::NONE => $this->lang->txt('grading_revised'),
            },
        ];

        $options['correction_status'] = [
            CorrectionStatus::OPEN->value => $this->lang->txt('correction_status_open'),
            CorrectionStatus::APPROXIMATION->value => $this->lang->txt('correction_status_approximation'),
            CorrectionStatus::CONSULTING->value => $this->lang->txt('correction_status_consulting'),
            CorrectionStatus::STITCH->value => $this->lang->txt('correction_status_stitch'),
            CorrectionStatus::FINALIZED->value => $this->lang->txt('correction_status_finalized'),
        ];

        if ($this->settings->getRequiredCorrectors() == 1) {
            unset($options['grading_status'][ GradingStatus::REVISED->value]);
            unset($options['position']);
            unset($options['other_status']);
            unset($options['correction_status']);
        } else {
            if (!$this->settings->getProcedureWhenDistance() || $this->settings->getProcedure() !== CorrectionProcedure::APPROXIMATION) {
                unset($options['correction_status'][ CorrectionStatus::APPROXIMATION->value]);
            }
            if (!$this->settings->getProcedureWhenDistance() || $this->settings->getProcedure() !== CorrectionProcedure::CONSULTING) {
                unset($options['correction_status'][ CorrectionStatus::CONSULTING->value]);
            }
        }

        return $options;
    }

    public function getFilterValues(): array
    {
        $corrector_id = $this->correctors->oneByUserId($this->user_id)?->getId() ?? 0;
        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $getter = [
            'position' => $prefs->getFilterAssignedPosition(...),
            'grading_status' => $prefs->getFilterGradingStatus(...),
            'other_status' => $prefs->getFilterOtherStatus(...),
            'correction_status' => $prefs->getFilterCorrectionStatus(...),
        ];

        $filter_values = [];
        foreach ($this->getFilterOptions() as $key => $options) {
            if ($this->settings->getStartpageFixed()) {
                $values[$key] = null;
            }
            $raw = $getter[$key]();
            if ($raw === null) {
                $filter_values[$key] = null;
            } else {
                $values = explode(',', $raw);
                $values = array_filter($values, fn($val) => isset($options[$val]));
                $filter_values[$key] = $values;
            }
        }

        return $filter_values;
    }

    public function saveFilterValues(array $filter_values): void
    {
        $corrector_id = $this->correctors->oneByUserId($this->user_id)?->getId() ?? 0;
        $prefs = $this->repos->correctorPrefs()->one($corrector_id) ??
            $this->repos->correctorPrefs()->new()->setCorrectorId($corrector_id);

        $setter = [
            'position' => $prefs->setFilterAssignedPosition(...),
            'grading_status' => $prefs->setFilterGradingStatus(...),
            'other_status' => $prefs->setFilterOtherStatus(...),
            'correction_status' => $prefs->setFilterCorrectionStatus(...),
        ];

        foreach ($this->getFilterOptions() as $key => $options) {
            $values = $filter_values[$key] ?? null;
            if (is_array($values)) {
                $values = array_filter($values, fn($val) => isset($options[$val]));
                $setter[$key](implode(',', $values));
            } else {
                $setter[$key](null);
            }
        }

        $this->repos->correctorPrefs()->save($prefs);
    }

    private function sortItems(CorrectionItem $left, CorrectionItem $right): int
    {
        $primary = $this->settings->getFixedSort1();
        $secondary = $this->settings->getFixedSort2();

        return $this->compareItems($left, $right, $primary)
            ?: $this->compareItems($left, $right, $secondary)
                ?: $this->compareItems($left, $right, CorrectionSort::PSEUDONYM_ASC);
    }

    private function compareItems(CorrectionItem $left, CorrectionItem $right, CorrectionSort $sort): int
    {
        return match ($sort) {
            CorrectionSort::GRADING_POSITION => GradingPosition::order(
                $left->getAssignment()->getPosition(),
                $right->getAssignment()->getPosition()
            ),
            CorrectionSort::OWN_STATE => GradingStatus::order(
                $left->getSummary()?->getGradingStatus() ?? GradingStatus::NOT_STARTED,
                $right->getSummary()?->getGradingStatus() ?? GradingStatus::NOT_STARTED,
            ),
            CorrectionSort::PSEUDONYM_ASC => strnatcasecmp(
                $left->getWriter()?->getPseudonym() ?? '',
                $right->getWriter()?->getPseudonym() ?? ''
            ),
            CorrectionSort::PSEUDONYM_DESC => strnatcasecmp(
                $right->getWriter()?->getPseudonym() ?? '',
                $left->getWriter()?->getPseudonym() ?? ''
            ),
        };
    }

    public function getCorrectorData(CorrectionItem $item): array
    {
        return [
            'task_id' => $item->getAssignment()->getTaskId(),
            'writer_id' => $item->getAssignment()->getWriterId(),
            'pseudonym' => $item->getWriter()->getPseudonym(),
            'position' => $item->getAssignment()->getPosition(),
            'position_text' => $this->format->gradingPosition($item->getAssignment()->getPosition()),
            'task_title' => $item->getTaskTitle(),
            'correction_status' => $item->getWriter()->getCorrectionStatus(),
            'correction_status_text' => $this->assessment_format->combinedStatus($item->getWriter()),
            'grading_status' => $item->getSummary()?->getGradingStatus(),
            'grading_status_text' => $this->format->gradingStatus($item->getSummary()?->getGradingStatus(), true),
            'grading_points' => $item->getSummary()?->getEffectivePoints(),
            'grading_grade' => $item->getSummary() ? $this->assessment_grading->getGradLevelForPoints($item->getSummary()->getPoints())?->getGrade() : null,
            'other_status' => $item->getOtherSummary()->getGradingStatusLight(),
            'other_status_text' => $this->otherStatusText($item),
            'other_points' => $item->getOtherSummary()?->getEffectivePointsLight(),
            'other_correction_text' => $this->otherCorrectionText($item),
            'final_result_text' => $this->assessment_format->finalResult($item->getWriter()),
            'final_points' => $item->getWriter()->getFinalPoints(),
            'final_grade' => $this->assessment_grading->getGradLevelForPoints($item->getWriter()->getFinalPoints())?->getGrade(),
            'can_correct' => $this->process->canCorrect($item->getAssignment()),
            'can_authorize' => $this->process->canAuthorizeOwnCorrection($item->getAssignment()),
            'can_revise' => $this->process->canRevise($item->getAssignment()),
            'can_remove_own_authorization' => $this->process->canRemoveOwnAuthorization($item->getAssignment()),
            'can_remove_first_authorization' => $this->process->canRemoveFirstAuthorization($item->getAssignment())
        ];
    }

    public function getAdminData(Writer $writer, Settings $task): array
    {
        return [
            'task_id' => $task->getTaskId(),
            'writer_id' => $writer->getId(),
            'pseudonym' => $writer->getPseudonym(),
            'position' => null,
            'position_text' => '',
            'task_title' => $task->getTitle(),
            'correction_status' => $writer->getCorrectionStatus(),
            'correction_status_text' => $this->assessment_format->combinedStatus($writer),
            'grading_status' => null,
            'grading_status_text' => null,
            'grading_points' => null,
            'grading_grade' => null,
            'other_status' => null,
            'other_status_text' => null,
            'other_points' => null,
            'other_correction_text' => null,
            'final_result_text' => null,
            'final_points' => null,
            'final_grade' => null,
            'can_correct' => null,
            'can_authorize' => null,
            'can_revise' => null,
            'can_remove_own_authorization' => null,
            'can_remove_first_authorization' => null
        ];
    }

    private function otherCorrectionText(CorrectionItem $item): string
    {
        if (empty($item->getOtherCorrector()) || empty($item->getOtherAssignment())) {
            return $this->lang->txt('grading_pos_empty');
        }
        return $this->lang->txt($item->getOtherAssignment()->getPosition()->languageVariable()) . ": "
            . $item->getOtherCorrector()->getListname(false) . ' - ' . $this->format->correctionResult($item->getOtherSummary(), false);
    }

    private function otherStatusText(CorrectionItem $item): string
    {
        return $this->format->gradingStatus(
            $item->getOtherSummary()?->getGradingStatusLight() ?? GradingStatus::OPEN, false);
    }
}
