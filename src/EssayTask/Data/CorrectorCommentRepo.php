<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface CorrectorCommentRepo
{
    public function new(): CorrectorComment;
    public function one(int $id): ?CorrectorComment;
    public function hasByEssayId(int $essay_id): bool;
    /** @return CorrectorComment[] */
    public function allByEssayIdAndCorrectorId(int $essay_id, int $corrector_id): array;
    public function save(CorrectorComment $entity): void;
    public function delete(int $id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
    public function deleteByEssayId(int $essay_id): void;
    public function deleteByEssayIdAndCorrectorId(int $essay_id, int $corrector_id): void;
}