<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Task\Data\CorrectorComment;

interface ReadService
{
    /** @return CorrectorComment[] */
    public function allByIds(int $task_id, int $writer_id, int $corrector_id): array;

    /**
     * @param CorrectorComment[] $comments
     * @return CorrectorComment[]
     */
    public function filterAndLabel(array $comments, int $parent_no): array;
}
