<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;

interface TokenRepo
{
    public function new(): Token;
    public function oneByIdsAndPurpose(int $user_id, int $ass_id, TokenPurpose $purpose): ?Token;
    public function save(Token $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
    public function deleteByIdsAndPurpose(int $user_id, int $ass_id, TokenPurpose $purpose): void;
}
