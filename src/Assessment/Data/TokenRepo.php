<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface TokenRepo
{
    public function new(): Corrector;
    public function oneByUserIdAndAssId(int $user_id, int $ass_id): ?Corrector;
    public function save(Corrector $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
    public function deleteByUserIdAndAssId(int $user_id, int $ass_id): void;
}
