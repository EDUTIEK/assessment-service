<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

interface ReadService
{
    public function oneById(int $id): ?CorrectorAssignment;
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
    public function allByCorrectorId(int $corrector_id): array;

    /**
     * @return CorrectorAssignment[]
     */
    public function allByCorrectorIdFiltered(int $corrector_id): array;
    public function countMissingCorrectors();
}
