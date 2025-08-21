<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Task\Data\CorrectorComment;

interface FullService
{
    /** @return CorrectorComment[] */
    public function allByCorrectorId(int $corrector_id): array;
    public function new(): CorrectorComment;
    public function save(CorrectorComment $comment): void;
    public function delete(): void;
}
