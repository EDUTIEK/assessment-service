<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

interface ReadService
{
    public function oneById(int $id): ?CorrectorAssignment;

    public function oneByIds(int $writer_id, int $corrector_id, int $task_id): ?CorrectorAssignment;

    /**
     * @return CorrectorAssignment[]
     */
    public function all(): array;
    /**
     * @return CorrectorAssignment[]
     */
    public function allByWriterId(int $writer_id): array;

    /**
     * @return CorrectorAssignment[]
     */
    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array;

    /**
     * @return CorrectorAssignment[]
     */
    public function allByCorrectorId(int $corrector_id, bool $only_authorized_writings = false): array;

    /**
     * Get an info message about missing assignments
     */
    public function getMissingAssignmentsInfo(): ?string;
}
