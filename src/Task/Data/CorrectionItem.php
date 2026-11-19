<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;
use Edutiek\AssessmentService\System\Data\UserData;

/**
 * Data needed for an entry in the item list of a corrector
 */
class CorrectionItem
{
    private ?UserData $user;

    public function __construct(
        private readonly int $assignment_id,
        private readonly Writer $writer,
        private readonly CorrectorAssignment $assignment,
        private readonly ?CorrectorSummary $summary,
        private readonly ?string $task_title,
        private readonly ?CorrectorSummary $other_summary,
        private readonly ?UserData $other_corrector,
        private readonly ?CorrectorAssignment $other_assignment
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

    /**
     * Get the user data of the writer
     * must only be used internally in the Correction item service
     * @return UserData|null
     */
    public function getUser(): ?UserData
    {
        return $this->user;
    }

    /**
     * Set the user data of the writer
     * must only be used internally in the Correction item service
     * @return UserData|null
     */
    public function setUser(?UserData $user): self
    {
        $this->user = $user;
        return $this;
    }
}
