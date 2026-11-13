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

readonly class Service implements ReadService
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
    public function getItems(bool $filtered): array
    {
        $corrector = $this->correctors->oneByUserId($this->user_id);

        $assignments = [];
        if ($corrector) {
            $assignments = $filtered
                ? $this->assignments->allByCorrectorIdFiltered($corrector->getId(), true)
                : $this->assignments->allByCorrectorId($corrector->getId(), true);
        }

        return array_map(fn(CorrectorAssignment $assignment) => $this->buildCorrectionItem($assignment), $assignments);
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

    public function getDisplayData(CorrectionItem $item): array
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
            'other_status_text' => $this->otherStatusText($item),
            'other_points' => $item->getOtherSummary()?->getEffectivePointsLight(),
            'other_correction_text' => $this->otherCorrectionText($item),
            'final_result_text' => $this->assessment_format->finalResult($item->getWriter()),
            'final_points' => $item->getWriter()->getFinalPoints(),
            'final_grade' => $this->assessment_grading->getGradLevelForPoints($item->getWriter()->getFinalPoints())?->getGrade(),
            'can_correct' => $this->process->canCorrect($item->getAssignment()),
            'can_authorize' => $this->process->canAuthorizeOwnCorrection($item->getAssignment()),
            'can_revise' => $this->process->canRevise($item->getAssignment()),
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
            'other_status_text' => null,
            'other_points' => null,
            'other_correction_text' => null,
            'final_result_text' => null,
            'final_points' => null,
            'final_grade' => null,
            'can_correct' => null,
            'can_authorize' => null,
            'can_revise' => null,
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
        if (empty($item->getOtherSummary())) {
            return '';
        }
        return $this->format->gradingStatus($item->getOtherSummary()?->getGradingStatus(), false);
    }
}
