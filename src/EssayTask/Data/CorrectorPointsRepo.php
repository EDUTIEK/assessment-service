<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface CorrectorPointsRepo
{
    public function new(): CorrectorPoints;
    public function one(int $id): ?CorrectorPoints;
    public function hasByEssayId(int $essay_id): bool;
    /** @return CorrectorPoints[] */
    public function allByEssayIdAndCorrectorId(int $essay_id, int $corrector_id): array;
    public function save(CorrectorPoints $entity): void;
    public function delete(int $id): void;
    public function deleteByCriterionId(int $essay_id): void;
    public function deleteByEssayId(int $essay_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
    public function deleteByEssayIdAndCorrectorId(int $essay_id, int $corrector_id): void;
}