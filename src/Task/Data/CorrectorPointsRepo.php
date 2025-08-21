<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface CorrectorPointsRepo
{
    public function new(): CorrectorPoints;
    public function one(int $id): ?CorrectorPoints;
    public function hasByTaskIdAndWriterId(int $task_id, int $writer_id): bool;
    /** @return CorrectorPoints[] */
    public function allByTaskIdAndWriterIdAndCorrectorId(int $task_id, int $writer_id, int $corrector_id): array;
    public function save(CorrectorPoints $entity): void;
    public function delete(int $id): void;
    public function deleteByCriterionId(int $criterion_id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByTaskIdAndWriterId(int $task_id, int $writer_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
    public function deleteByTaskIdAndWriterIdAndCorrectorId(int $task_id, int $writer_id, int $corrector_id): void;
}