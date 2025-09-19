<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface CorrectorSummaryRepo
{
    public function new(): CorrectorSummary;
    public function one(int $id): ?CorrectorSummary;
    public function hasByTaskIdAndWriterId(int $task_id, int $writer_id): bool;
    public function hasAuthorizedByAssId(int $ass_id, ?int $corrector_id = null): bool;
    /** @return CorrectorSummary[] */
    public function allByAssId(int $ass_id): array;
    /** @return CorrectorSummary[] */
    public function allByWriterId(int $writer_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskId(int $task_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndWriterIds(int $task_id, array $writer_ids): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndCorrectorId(int $task_id, int $corrector_id): array;
    public function allByCorrectorId(int $corrector_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndWriterIdAndCorrectorId(int $task_id, int $writer_id, int $corrector_id): array;
    public function save(CorrectorSummary $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByTaskIdAndWriterId(int $task_id, int $writer_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
    public function deleteByTaskIdAndWriterIdAndCorrectorId(int $task_id, int $writer_id, int $corrector_id): void;

    public function moveCorrectorByTaskIdAndWriterId(
        int $task_id,
        int $writer_id,
        int $from_corrector,
        int $to_corrector
    );
}