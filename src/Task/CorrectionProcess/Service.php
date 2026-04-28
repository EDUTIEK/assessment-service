<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as WholeProcessService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\LogEntry\MentionUser;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\LogEntry\Type as LogEntryType;
use Edutiek\AssessmentService\Assessment\Notification\DeliverService as NotificationService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as AssignmentsService;
use Edutiek\AssessmentService\Task\CorrectorSummary\ReadService as SummaryService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;

readonly class Service implements FullService
{
    public function __construct(
        private int $user_id,
        private Repositories $repos,
        private AssignmentsService $assignments,
        private WriterService $writer_service,
        private CorrectorService $corrector_service,
        private WholeProcessService $whole_process,
        private LogEntryService $log_entry,
        private NotificationService $notification_service,
        private CorrectionSettings $correction_settings,
        private SummaryService $summary_service,
        private LanguageService $language,
    ) {
    }

    public function canCorrect(CorrectorAssignment $assignment): bool
    {
        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());
        if (!$writer->canBeCorrected()) {
            return false;
        }
        if ($assignment->getPosition()->isCorrector() && $writer->getCorrectionStatus() !== CorrectionStatus::OPEN) {
            return false;
        }
        if ($assignment->getPosition()->isStitch() && $writer->getCorrectionStatus() !== CorrectionStatus::STITCH) {
            return false;
        }

        if ($assignment->getPosition() === GradingPosition::SECOND && $this->correction_settings->getWaitForFirst()
        ) {
            $first = $this->repos->correctorAssignment()->oneByPosition(
                $assignment->getTaskId(),
                $assignment->getWriterId(),
                GradingPosition::FIRST
            );
            $first_summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                $first?->getTaskId(),
                $first?->getWriterId(),
                $first?->getCorrectorId()
            );
            if ($first_summary === null || !$first_summary->isAuthorized()) {
                return false;
            }
        }

        $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        );

        if ($summary?->isAuthorized()) {
            return false;
        }

        return true;
    }

    public function canAuthorizeOwnCorrection(CorrectorAssignment $assignment): bool
    {
        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());
        if (!$writer->canBeCorrected()) {
            return false;
        }
        if ($assignment->getPosition()->isCorrector() && $writer->getCorrectionStatus() !== CorrectionStatus::OPEN) {
            return false;
        }
        if ($assignment->getPosition()->isStitch() && $writer->getCorrectionStatus() !== CorrectionStatus::STITCH) {
            return false;
        }

        if ($assignment->getPosition() === GradingPosition::SECOND) {
            $first = $this->repos->correctorAssignment()->oneByPosition(
                $assignment->getTaskId(),
                $assignment->getWriterId(),
                GradingPosition::FIRST
            );
            $first_summary = $this->summary_service->getForAssignment($first);
            if (!$first_summary->isAuthorized()) {
                return false;
            }
        }

        $summary = $this->summary_service->getForAssignment($assignment);
        if ($summary->isAuthorized()) {
            return false;
        }

        return true;
    }

    public function canRemoveOwnAuthorization(CorrectorAssignment $assignment): bool
    {
        if (!$this->correction_settings->getUndoAuthorization()) {
            return false;
        }

        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());
        if ($writer->getCorrectionStatus() !== CorrectionStatus::OPEN) {
            return false;
        }
        $summary = $this->summary_service->getForAssignment($assignment);
        if (!$summary->isAuthorized()) {
            return false;
        }

        return true;
    }

    public function canRemoveFirstAuthorization(CorrectorAssignment $assignment): bool
    {
        if (!$this->correction_settings->getUndoFirstAuthorization()) {
            return false;
        }

        if ($assignment->getPosition() !== GradingPosition::SECOND) {
            return false;
        }

        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());
        if ($writer->getCorrectionStatus() !== CorrectionStatus::OPEN) {
            return false;
        }

        foreach ($this->assignments->allByTaskIdAndWriterId($assignment->getTaskId(), $assignment->getWriterId()) as $ass) {
            $summary = $this->summary_service->getForAssignment($ass);

            if ($ass->getPosition() === GradingPosition::FIRST && !$summary->isAuthorized()) {
                return false;
            }
            if ($ass->getPosition() !== GradingPosition::FIRST && $summary->isAuthorized()) {
                return false;
            }
        }

        return true;
    }

    public function canRevise(CorrectorAssignment $assignment): bool
    {
        if (!$assignment->getPosition()->isCorrector()) {
            return false;
        }
        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());
        if (!$writer->getWritingAuthorized()) {
            return false;
        }
        $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        );
        if ($summary === null || !$summary->isAuthorized() || $summary->isRevised()) {
            return false;
        }

        switch ($writer->getCorrectionStatus()) {
            case CorrectionStatus::APPROXIMATION:
                if ($assignment->getPosition() === GradingPosition::FIRST) {
                    return true;
                }
                $first = $this->repos->correctorAssignment()->oneByPosition(
                    $assignment->getTaskId(),
                    $assignment->getWriterId(),
                    GradingPosition::FIRST
                );
                $first_summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                    $first?->getTaskId(),
                    $first?->getWriterId(),
                    $first?->getCorrectorId()
                );
                if ($first_summary === null || !$first_summary->isRevised()) {
                    return false;
                }

                // no break
            case CorrectionStatus::CONSULTING:
                // both correctors must change the points
                // only second corrector should enter a revision text
                return true;

            default:
                return false;
        }

        return true;
    }

    public function authorizeOwnCorrection(CorrectorAssignment $assignment): Result
    {
        if (!$this->canAuthorizeOwnCorrection($assignment)) {
            return new Result(false, $this->language->txt('authorization_not_allowed'));
        }

        // use clone to allow compare with a previous version
        $summary = clone $this->summary_service->getForAssignment($assignment);
        $summary->setGradingStatus(GradingStatus::AUTHORIZED, $this->user_id);

        return $this->checkAndSaveSummary($summary);
    }

    public function removeOwnAuthorization(CorrectorAssignment $assignment): Result
    {
        if (!$this->canRemoveOwnAuthorization($assignment)) {
            return new Result(false, $this->language->txt('remove_authorization_not_allowed'));
        }

        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());

        $summary = $this->summary_service->getForAssignment($assignment);
        $summary->setGradingStatus(GradingStatus::OPEN, $this->user_id);
        $this->repos->correctorSummary()->save($summary);

        // notify the second corrector about a removed authorization of the first corrector
        if ($assignment->getPosition() === GradingPosition::FIRST) {
            foreach ($this->assignments->allByTaskIdAndWriterId($assignment->getTaskId(), $assignment->getWriterId()) as $ass) {
                if ($ass->getPosition() === GradingPosition::SECOND) {
                    $corrector2 = $this->corrector_service->oneById($ass->getCorrectorId());
                    $this->notification_service->createFor(NotificationType::CORRECTOR_FIRST_AUTHORIZATION_REMOVED, $writer, $corrector2);
                    break;
                }
            }
        }

        // this should not write a log entry in version 10+

        return new Result(true);
    }

    public function removeFirstAuthorization(CorrectorAssignment $assignment, string $reason): Result
    {
        if (!$this->canRemoveFirstAuthorization($assignment)) {
            return new Result(false, $this->language->txt('remove_first_authorization_not_allowed'));
        }

        $writer = $this->writer_service->oneByWriterId($assignment->getWriterId());

        foreach ($this->assignments->allByTaskIdAndWriterId($assignment->getTaskId(), $assignment->getWriterId()) as $ass) {
            if ($ass->getPosition() === GradingPosition::FIRST) {

                $summary = $this->summary_service->getForAssignment($ass);
                $summary->setGradingStatus(GradingStatus::OPEN, $this->user_id);
                $this->repos->correctorSummary()->save($summary);

                $corrector1 = $this->corrector_service->oneById($ass->getCorrectorId());
                $this->notification_service->createFor(NotificationType::CORRECTOR_AUTHORIZATION_REMOVED, $writer, $corrector1, $reason);
                break;
            }
        }

        // this should not write a log entry in version 10+

        return new Result(true);
    }

    public function getRemovableStepsOptions(array $writing_tasks): array
    {
        $multi = $this->correction_settings->hasMultipleCorrectors();
        $procedure = $this->correction_settings->getProcedure();

        $steps = [];
        foreach ($writing_tasks as $wt) {
            foreach ($this->assignments->allByTaskIdAndWriterId($wt->getTaskId(), $wt->getWriterId()) as $assignment) {
                $summary = $this->summary_service->getForAssignment($assignment);
                if ($summary->isAuthorized()) {
                    $step = match($assignment->getPosition()) {
                        GradingPosition::FIRST => ProcessStep::FIRST_AUTHORIZATION,
                        GradingPosition::SECOND => ProcessStep::SECOND_AUTHORIZATION,
                        GradingPosition::STITCH => ProcessStep::STITCH_DECISION,
                    };
                    $steps[$step->value] = $this->language->txt($step->langVar($multi, $procedure));
                }
                if ($summary->isRevised()) {
                    $step = match($assignment->getPosition()) {
                        GradingPosition::FIRST => ProcessStep::FIRST_REVISION,
                        GradingPosition::SECOND => ProcessStep::SECOND_REVISION,
                        default => null
                    };
                    if ($step) {
                        $steps[$step->value] = $this->language->txt($step->langVar($multi, $procedure));
                    }
                }
            }
        }

        krsort($steps);
        return $steps;
    }

    public function removeEqualOrHigherSteps(int $task_id, Writer $writer, ProcessStep $start_step, ?string $reason): Result
    {
        $changed = false;

        foreach ($this->assignments->allByTaskIdAndWriterId($task_id, $writer->getId()) as $assignment) {
            $summary = $this->summary_service->getForAssignment($assignment);
            $summary_changed = false;

            if ($summary->isAuthorized()) {
                $step = match($assignment->getPosition()) {
                    GradingPosition::FIRST => ProcessStep::FIRST_AUTHORIZATION,
                    GradingPosition::SECOND => ProcessStep::SECOND_AUTHORIZATION,
                    GradingPosition::STITCH => ProcessStep::STITCH_DECISION,
                };
                if ($step->isHigherOrEqualThan($start_step)) {
                    $summary->setPreGraded(new \DateTimeImmutable());
                    $summary->setCorrectionAuthorized(null);
                    $summary->setCorrectionAuthorizedBy(null);
                    $summary_changed = true;
                }
            }

            if ($summary->isRevised()) {
                $step = match($assignment->getPosition()) {
                    GradingPosition::FIRST => ProcessStep::FIRST_REVISION,
                    GradingPosition::SECOND => ProcessStep::SECOND_REVISION,
                    default => null
                };
                if ($step?->isHigherOrEqualThan($start_step)) {
                    $summary->setRevised(null);
                    $summary_changed = true;
                }
            }

            if ($summary_changed) {
                $this->repos->correctorSummary()->save($summary->touch());
                $changed = true;

                $corrector = $this->corrector_service->oneById($summary->getCorrectorId());
                if ($corrector !== null && $corrector->getUserId() !== $this->user_id) {
                    $this->notification_service->createFor(
                        NotificationType::CORRECTOR_AUTHORIZATION_REMOVED,
                        $writer,
                        $corrector,
                        $reason
                    );
                }
            }
        }

        if ($changed) {

            switch ($start_step) {
                case ProcessStep::FIRST_AUTHORIZATION:
                case ProcessStep::SECOND_AUTHORIZATION:
                    $status = CorrectionStatus::OPEN;
                    break;
                case ProcessStep::FIRST_REVISION:
                case ProcessStep::SECOND_REVISION:
                    $status = match($this->correction_settings->getProcedure()) {
                        CorrectionProcedure::APPROXIMATION => CorrectionStatus::APPROXIMATION,
                        CorrectionProcedure::CONSULTING => CorrectionStatus::CONSULTING,
                        default => CorrectionProcedure::CONSULTING
                    };
                    break;
                case ProcessStep::STITCH_DECISION:
                    $status = CorrectionStatus::STITCH;
                    break;
            }

            $this->whole_process->resetStatus($writer, $status);

            $this->log_entry->addEntry(
                LogEntryType::CORRECTION_REMOVE_AUTHORIZATION,
                MentionUser::fromSystem($this->user_id),
                MentionUser::fromWriter($writer),
                $reason
            );
            return new Result(true);
        }

        return new Result(false, $this->language->txt('authorizations_not_found'));
    }

    public function checkAndSaveSummary(CorrectorSummary $summary): Result
    {
        $result = new Result();

        $assignment = $this->repos->correctorAssignment()->oneByIds(
            $summary->getWriterId(),
            $summary->getCorrectorId(),
            $summary->getTaskId()
        );
        $writer = $this->writer_service->oneByWriterId($summary->getWriterId());
        if ($assignment === null || $writer === null) {
            $result->addFailure($this->language->txt('assignment_or_writer_not_found'));
        }

        $old = $this->summary_service->getForAssignment($assignment);
        $failures = [];

        if ($summary->getGradingStatus() !== $old->getGradingStatus()) {
            switch ($summary->getGradingStatus()) {
                case GradingStatus::NOT_STARTED:
                case GradingStatus::OPEN:
                case GradingStatus::PRE_GRADED:
                    if ($old->isAuthorized() || $old->isRevised()) {
                        $result->addFailure($this->language->txt('authorization_not_removable'));
                    }
                    break;
                case gradingStatus::AUTHORIZED:
                    if (!$this->canAuthorizeOwnCorrection($assignment)) {
                        $result->addFailure($this->language->txt('authorization_not_allowed'));
                    }
                    if ($summary->getPoints() === null) {
                        $result->addFailure($this->language->txt('points_missing'));
                    }
                    if (empty($summary->getSummaryText()) && empty($summary->getSummaryPdf())) {
                        $result->addFailure($this->language->txt('authorization_text_missing'));
                    }
                    break;
                case GradingStatus::REVISED:
                    if (!$this->canRevise($assignment)) {
                        $result->addFailure($this->language->txt('revision_not_allowed'));
                    }
                    if ($summary->getRevisionPoints() === null) {
                        $result->addFailure($this->language->txt('points_missing'));
                    }
                    if (empty($summary->getRevisionText())
                        && $assignment->getPosition()->canEnterRevisionText($this->correction_settings->getProcedure())) {
                        $result->addFailure($this->language->txt('revision_text_missing'));
                    }
                    break;
            }
        }

        if (($summary->getSummaryText() != $old->getSummaryText() || $summary->getPoints() != $old->getPoints()
            || $summary->getSummaryPdf() != $old->getSummaryPdf())
            && ($old->isAuthorized() || $old->isRevised())) {
            $result->addFailure($this->language->txt('changing_correction_not_allowed'));
        }

        if (($summary->getRevisionText() != $old->getRevisionText() || $summary->getRevisionPoints() != $old->getRevisionPoints()
            || $summary->getRequireOtherRevision() != $old->getRequireOtherRevision())
            && (!$old->isAuthorized() || $old->isRevised())) {
            $result->addFailure($this->language->txt('revision_not_allowed'));
        }

        if ($summary->getPoints() > $this->correction_settings->getMaxPoints()
            || $summary->getRevisionPoints() > $this->correction_settings->getMaxPoints()) {
            $result->addFailure($this->language->txt('points_exceed_maximum'));
        }

        if ($this->correction_settings->getNoManualDecimals()
            && (floor($summary->getPoints() ?? 0) < $summary->getPoints() ?? 0 ||
                floor($summary->getRevisionPoints() ?? 0) < $summary->getRevisionPoints() ?? 0)
        ) {
            $result->addFailure($this->language->txt('points_must_not_have_decimals'));
        }

        if ($result->isFailed()) {
            return $result;
        }

        $this->repos->correctorSummary()->save($summary->touch());

        if ($old->isAuthorized() && !$summary->isAuthorized() && !$summary->isRevised()) {
            $this->log_entry->addEntry(
                LogEntryType::CORRECTION_REMOVE_AUTHORIZATION,
                MentionUser::fromSystem($this->user_id),
                MentionUser::fromWriter($writer)
            );
        }

        $status = $this->whole_process->updateStatus($writer);

        if ($status === CorrectionStatus::APPROXIMATION || $status === CorrectionStatus::CONSULTING) {
            if (!$old->isRevised() && $summary->isRevised() && $summary->getRequireOtherRevision()) {
                foreach ($this->assignments->allByTaskIdAndWriterId($summary->getTaskId(), $summary->getWriterId()) as $assignment) {
                    if ($assignment->getPosition() === GradingPosition::SECOND) {
                        $corrector = $this->corrector_service->oneById($assignment->getCorrectorId());
                        $this->notification_service->createFor(NotificationType::CORRECTOR_PROCEDURE_STARTED, $writer, $corrector);
                    }
                }
            }
        }

        return $result;
    }
}
