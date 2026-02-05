<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\Task\Api\ApiException;

readonly class Service implements ReadService
{
    public function __construct(
        private int $ass_id,
        private int $usr_id,
        private Repositories $repos
    ) {
    }

    public function allByIds(int $task_id, int $writer_id, int $corrector_id): array
    {
        return $this->repos->correctorComment()->allByTaskIdAndWriterIdAndCorrectorId($task_id, $writer_id, $corrector_id);
    }

    /**
     * @param CorrectorComment[] $comments
     * @return CorrectorComment[]
     */
    public function filterAndLabel(array $comments, int $parent_no): array
    {
        $sort = [];
        foreach ($comments as $comment) {
            if ($comment->getParentNumber() == $parent_no) {
                $key = sprintf('%06d', $comment->getStartPosition()) . $comment->getKey();
                $sort[$key] = $comment;
            }
        }
        ksort($sort);

        $result = [];
        $number = 1;
        foreach ($sort as $comment) {
            // only comments with details to show should get a label
            // others are only marks in the text
            if ($comment->hasDetailsToShow()) {
                $result[] = $comment->withLabel($parent_no . '.' . $number++);
            } else {
                $result[] = $comment;
            }
        }

        return $result;
    }
}
