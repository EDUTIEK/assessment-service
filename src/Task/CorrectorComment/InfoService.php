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
     * @param ?int $parent_no  number of the parent page or paragraph, or null to not filter the infos
     * @return CorrectorCommentInfo[]
     */
    public function filterAndLabelInfos(array $infos, int $parent_no): array;

    /**
     * Get the Symbol that should be shown in a label
     */
    public function getSymbolForLabel(string $symbol): string;

    /**
     * Get the text that should be shown for a symbol
     */
    public function getSymbolText(string $symbol): string;
}
