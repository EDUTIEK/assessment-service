<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface TokenRepo
{
    public function new(): Token;
    public function oneByUserIdAndAssId(int $user_id, int $ass_id): ?Token;
    public function save(Token $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
    public function deleteByUserIdAndAssId(int $user_id, int $ass_id): void;
}
