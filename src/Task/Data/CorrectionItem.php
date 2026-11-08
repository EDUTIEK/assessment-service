<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;
use Edutiek\AssessmentService\System\Data\UserData;

/**
 * Data needed for an entry in the item list of a corrector
 */
readonly class CorrectionItem
{
    public function __construct(
        private int $assignment_id,
        private Writer $writer,
        private CorrectorAssignment $assignment,
        private ?CorrectorSummary $summary,
        private ?string $task_title,
        private ?CorrectorSummary $other_summary,
        private ?UserData $other_corrector,
        private ?CorrectorAssignment $other_assignment
    ) {
    }

    public function getAssignmentId(): int
    {
        return $this->assignment_id;
    }

    public function getWriter(): Writer
    {
        return $this->writer;
    }

    public function getAssignment(): CorrectorAssignment
    {
        return $this->assignment;
    }

    public function getSummary(): ?CorrectorSummary
    {
        return $this->summary;
    }

    public function getOtherSummary(): ?CorrectorSummary
    {
        return $this->other_summary;
    }

    public function getOtherCorrector(): ?UserData
    {
        return $this->other_corrector;
    }

    public function getTaskTitle(): ?string
    {
        return $this->task_title;
    }

    public function getOtherAssignment(): ?CorrectorAssignment
    {
        return $this->other_assignment;
    }
}
