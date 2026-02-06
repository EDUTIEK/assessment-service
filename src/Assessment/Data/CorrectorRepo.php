<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface CorrectorRepo
{
    public function new(): Corrector;
    public function one(int $id): ?Corrector;
    public function has(int $id): bool;
    public function hasReports();
    public function hasByCorrectorIdAndAssId(int $corrector_id, int $ass_id): bool;
    public function oneByUserIdAndAssId(int $user_id, int $ass_id): ?Corrector;
    /** @return Corrector[] */
    public function some(array $ids): array;
    /** @return Corrector[] */
    public function allByAssId(int $ass_id): array;
    /** @return Corrector[] */
    public function allByUserId(int $user_id): array;
    public function save(Corrector $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
}
