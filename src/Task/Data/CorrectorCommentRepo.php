<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface CorrectorCommentRepo
{
    public function new(): CorrectorComment;
    public function one(int $id): ?CorrectorComment;
    public function hasByAssId(int $ass_id): bool;
    public function hasByTaskIdAndWriterId(int $task_id, int $writer_id): bool;
    /** @return CorrectorComment[] */
    public function allByTaskIdAndWriterIdAndCorrectorId(int $task_id, int $writer_id, int $corrector_id): array;
    public function save(CorrectorComment $entity): void;
    public function delete(int $id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByTaskIdAndWriterId(int $task_id, int $writer_id): void;
    public function deleteByTaskIdAndWriterIdAndCorrectorId(int $task_id, int $writer_id, int $corrector_id): void;
}
