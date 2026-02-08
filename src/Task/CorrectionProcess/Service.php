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
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\CorrectorSummary\ReadService as SummaryService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Task\Data\Repositories;

readonly class Service implements FullService
{
    public function __construct(
        private int $user_id,
        private Repositories $repos,
        private WriterService $writer_service,
        private WholeProcessService $whole_process,
        private LogEntryService $log_entry,
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

    public function canAuthorize(CorrectorAssignment $assignment): bool
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

    public function authorizeCorrection(CorrectorAssignment $assignment): Result
    {
        if (!$this->canAuthorize($assignment)) {
            return new Result(false, $this->language->txt('authorization_not_allowed'));
        }

        // use clone to allow compare with a previous version
        $summary = clone $this->summary_service->getForAssignment($assignment);
        $summary->setGradingStatus(GradingStatus::AUTHORIZED, $this->user_id);

        return $this->checkAndSaveSummary($summary);
    }

    public function removeAuthorizations(int $task_id, Writer $writer): Result
    {
        $changed = false;

        if ($writer->getCorrectionStatus() === CorrectionStatus::STITCH ||
            $writer->isCorrectionFinalized() && $writer->getFinalizedFromStatus() === CorrectionStatus::STITCH) {
            return new Result(false, $this->language->txt('authorization_not_removable'));
        }

        // remove authorizations
        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterId($task_id, $writer->getId()) as $summary) {
            if ($summary->isAuthorized()) {
                $summary->setGradingStatus(GradingStatus::OPEN, $this->user_id);
                $this->repos->correctorSummary()->save($summary);
                $changed = true;
            }
        }

        if ($changed || $writer->isCorrectionFinalized()) {
            $this->whole_process->setCorrectionOpen($writer);

            $this->log_entry->addEntry(
                LogEntryType::CORRECTION_REMOVE_AUTHORIZATION,
                MentionUser::fromSystem($this->user_id),
                MentionUser::fromWriter($writer)
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
                    if (!$this->canAuthorize($assignment)) {
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
                    if (empty($summary->getRevisionText())) {
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
            && (floor($summary->getPoints() ?? 0) < $summary->getPoints() ?? 0)
                || floor($summary->getRevisionPoints() ?? 0) < $summary->getRevisionPoints() ?? 0) {
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

        $this->whole_process->updateStatus($writer);
        return $result;
    }
}
