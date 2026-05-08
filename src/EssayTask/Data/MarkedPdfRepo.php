<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface MarkedPdfRepo
{
    public function new(): MarkedPdf;
    public function oneByIds(int $task_id, int $writer_id, ?int $corrector_id): ?MarkedPdf;
    /** @return MarkedPdf[] */
    public function allByTaskId(int $task_id): array;
    /** @return MarkedPdf[] */
    public function allByWriterId(int $writer_id): array;
    /** @return MarkedPdf[] */
    public function allByCorrectorId(int $corrector_id): array;
    /** @return MarkedPdf[] */
    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array;
    public function save(MarkedPdf $entity): void;
    public function delete(int $id): void;
}
