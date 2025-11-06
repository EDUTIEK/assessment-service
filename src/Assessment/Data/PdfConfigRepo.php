<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface PdfConfigRepo
{
    public function new(): PdfConfig;
    public function one(int $id): ?PdfConfig;
    /** @return PdfConfig[] */
    public function allByAssIdAndPurpose(int $ass_id, string $purpose): array;
    public function save(PdfConfig $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
    public function deleteByAssIdAndPurpose(int $ass_id, string $purpose): void;
}
