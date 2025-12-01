<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as SettingsService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\Grading;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private SettingsService $settings_service,
        private WriterService $writer_service,
        private TaskManager $task_manager,
        private GradingProvider $grading_provider
    ) {
    }

    public function removeFinalization(Writer $writer)
    {
        // todo: determine the correct status (approcimation, stitch)
        // todo: log the action
        if ($writer->getCorrectionStatus() === CorrectionStatus::FINALIZED) {
            $writer->setCorrectionStatus(CorrectionStatus::OPEN);
            $writer->setCorrectionStatusChanged(new \DateTimeImmutable("now"));
            $writer->setCorrectionStatusChangedBy($this->user_id);
            $this->repos->writer()->save($writer);
        }
    }

    public function updateStatus(Writer $writer)
    {
        if ($writer->getCorrectionStatus() === CorrectionStatus::FINALIZED) {
            return;
        }

        $settings = $this->settings_service->get();
        $tasks = $this->task_manager->all();

        $sum_of_points = 0;
        $sum_of_weights = 0;
        foreach ($this->task_manager->all() as $task) {
            $gradings = $this->grading_provider->gradingsForTaskAndWriter($task->getId(), $writer->getId());

            list($status, $points) = $this->calculateTask($writer, $settings, $gradings);
            switch ($status) {
                case CorrectionStatus::OPEN:
                    // whole status will not change if a partial status is open
                    return;

                case CorrectionStatus::APPROXIMATION:
                case CorrectionStatus::CONSULTING:
                case CorrectionStatus::STITCH:
                    // this should only happen with single task => set the status directly
                    $this->writer_service->changeCorrectionStatus($writer, $status, $this->user_id);
                    return;

                case CorrectionStatus::FINALIZED:
                    $sum_of_points += $points * $task->getWeight();
                    $sum_of_weights += $task->getWeight();
                    break;
            }

            // all tasks are finalized, calculate the final points
            if ($sum_of_weights > 0) {
                $writer->setFinalPoints($sum_of_points / $sum_of_weights);
                $this->repos->writer()->save($writer);
                $this->writer_service->changeCorrectionStatus($writer, $status, $this->user_id);
            }
        }
    }

    /**
     * Calculate the correction status and points from the gradings of a single task
     * @param Grading[] $gradings
     * @return array{CorrectionStatus, float}
     */
    private function calculateTask(Writer $writer, CorrectionSettings $settings, array $gradings): array
    {
        $first = $gradings[GradingPosition::FIRST->value] ?? null;
        $second = $gradings[GradingPosition::SECOND->value] ?? null;
        $stitch = $gradings[GradingPosition::STITCH->value] ?? null;

        $first_points = $first?->getPoints() ?? 0;
        $second_points = $second?->getPoints() ?? 0;
        $distance = abs($first_points - $second_points);
        $average = ($first_points + $second_points) / 2;

        // single corrector
        if ($settings->getRequiredCorrectors() == 1) {
            if ($first?->isAuthorized()) {
                return [CorrectionStatus::FINALIZED, $first->getPoints()];
            } else {
                return [CorrectionStatus::OPEN, null];
            }
        }

        // multi corectors
        switch ($writer->getCorrectionStatus()) {
            case CorrectionStatus::OPEN:

                if ($first?->isAuthorized() && $second?->isAuthorized()) {
                    if ($settings->getProcedureWhenDistance()) {
                        if ($distance > $settings->getMaxAutoDistance()) {
                            switch ($settings->getProcedure()) {
                                case CorrectionProcedure::APPROXIMATION:
                                    return [CorrectionStatus::APPROXIMATION, null];

                                case CorrectionProcedure::CONSULTING:
                                    return [CorrectionStatus::CONSULTING, null];

                                case CorrectionProcedure::NONE:
                                    if ($settings->getStitchAfterProcedure()) {
                                        return [CorrectionStatus::STITCH, null];
                                    }
                            }
                        }
                    }
                    return [CorrectionStatus::FINALIZED, $average];
                }
                return [CorrectionStatus::OPEN, null];

            case CorrectionStatus::APPROXIMATION:
                if (!$first?->isRevised()) {
                    return [CorrectionStatus::APPROXIMATION, null];
                }
                if (!$second?->isRevised()) {
                    if ($first?->getRequireOtherRevision()) {
                        return [CorrectionStatus::APPROXIMATION, null];
                    } else {
                        return [CorrectionStatus::FINALIZED, $first->getPoints()];
                    }
                }
                if ($distance > $settings->getMaxAutoDistance() && $settings->getStitchAfterProcedure()) {
                    return [CorrectionStatus::STITCH, null];
                } else {
                    return [CorrectionStatus::FINALIZED, $average];
                }

                // no break
            case CorrectionStatus::CONSULTING:
                if (!$first?->isRevised() || !$second?->isRevised()) {
                    return [CorrectionStatus::CONSULTING, null];
                }
                if ($distance > $settings->getMaxAutoDistance() && $settings->getStitchAfterProcedure()) {
                    return [CorrectionStatus::STITCH, null];
                } else {
                    return [CorrectionStatus::FINALIZED, $average];
                }

                // no break
            case CorrectionStatus::STITCH:
                if (!$stitch->isAuthorized()) {
                    return [CorrectionStatus::STITCH, null];
                } else {
                    return [CorrectionStatus::FINALIZED, $stitch->getPoints()];
                }

                // no break
            case CorrectionStatus::FINALIZED:
            default:
                return [CorrectionStatus::FINALIZED, $writer->getFinalPoints()];
        }
    }
}
