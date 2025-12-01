<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;

interface CorrectorAssignmentRepo
{
    public function new(): CorrectorAssignment;
    public function hasByIds(int $writer_id, int $corrector_id, int $task_id): bool;
    public function oneByIds(int $writer_id, int $corrector_id, int $task_id): ?CorrectorAssignment;
    public function oneByPosition(int $task_id, int $writer_id, GradingPosition $position): ?CorrectorAssignment;
    /** @return CorrectorAssignment[] */
    public function allByTaskId(int $task_id): array;
    /** @return CorrectorAssignment[] */
    public function allByAssId(int $ass_id): array;
    /** @return CorrectorAssignment[] */
    public function allByWriterId(int $writer_id): array;
    /** @return CorrectorAssignment[] */
    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array;

    /** @return CorrectorAssignment[] */
    public function allByCorrectorId(int $corrector_id): array;
    public function save(CorrectorAssignment $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByWriterId(int $writer_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
    public function deleteByWriterIdAndCorrectorId(int $writer_id, int $corrector_id): void;
    public function oneById(int $id): ?CorrectorAssignment;

    public function deleteByTaskIdAndWriterId(int $task_id, mixed $writer_id);
}
