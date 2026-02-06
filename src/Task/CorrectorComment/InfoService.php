<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;

interface InfoService
{
    /**
     * @param GradingPosition[] $positions requested assignment positions
     * @return CorrectorCommentInfo[]
     */
    public function getInfos(int $task_id, int $writer_id, array $positions): array;

    /**
     * @param CorrectorCommentInfo[] $infos
     * @return CorrectorCommentInfo[]
     */
    public function filterAndLabelInfos(array $infos, int $parent_no): array;
}
