<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\CorrectorComment;

use Edutiek\AssessmentService\EssayTask\Data\CorrectorComment;

interface FullService
{
    /** @return CorrectorComment[] */
    public function allByCorrectorId(int $corrector_id): array;
    public function new(): CorrectorComment;
    public function save(CorrectorComment $comment): void;
    public function deleteByEssayId(int $id): void;
}
