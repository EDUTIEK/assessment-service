<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface MarkedPdfRepo
{
    public function new(): MarkedPdf;
    public function oneByIds(int $task_id, int $writer_id, ?int $corrector_id): ?MarkedPdf;
    /** @return MarkedPdf[] */
    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array;
    public function save(MarkedPdf $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByWriterId(int $writer_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
}
