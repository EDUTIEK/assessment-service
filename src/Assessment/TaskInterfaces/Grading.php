<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

/**
 * Shortened info about a single grading
 * This is used to calculate the overall correction status and points
 */
readonly class Grading
{
    public function __construct(
        private int $writer_id,
        private int $task_id,
        private int $corrector_id,
        private GradingPosition $position,
        private GradingStatus $status,
        private ?float $points,
        private bool $require_other_revision,
    ) {
    }

    public function getWriterId(): int
    {
        return $this->writer_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getCorrectorId(): int
    {
        return $this->corrector_id;
    }

    public function getPosition(): GradingPosition
    {
        return $this->position;
    }

    public function getStatus(): GradingStatus
    {
        return $this->status;
    }

    public function getPoints(): ?float
    {
        return $this->points;
    }

    public function getRequireOtherRevision(): bool
    {
        return $this->require_other_revision;
    }

    public function isAuthorized(): bool
    {
        return $this->status === GradingStatus::AUTHORIZED ;
    }

    public function isRevised(): bool
    {
        return $this->status === GradingStatus::REVISED;
    }
}
