<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface ExportFileRepo
{
    public function new(): ExportFile;
    public function one(int $id): ?ExportFile;

    public function hasByAssIdAndFileId(int $ass_id, string $file_id): bool;

    /** @return ExportFile[] */
    public function allByAssId(int $ass_id): array;

    /**
     * @param string[] $file_ids
     * @return ExportFile[]
     */
    public function allByAssIdAndFileIds(int $ass_id, array $file_ids): array;

    public function save(ExportFile $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
}
