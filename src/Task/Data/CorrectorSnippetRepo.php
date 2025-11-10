<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface CorrectorSnippetRepo
{
    public function new(): CorrectorSnippet;
    public function oneByKey(int $ass_id, int $corrector_id, string $key): ?CorrectorSnippet;
    public function allByCorrectorId(int $ass_id, int $corrector_id): array;
    public function save(CorrectorSnippet $entity): void;
    public function deleteByKey(int $ass_id, int $corrector_id, string $key): void;
    public function deleteByCorrectorId(int $ass_id, int $corrector_id): void;
}
