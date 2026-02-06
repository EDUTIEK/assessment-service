<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\EssayTask\Data\CommentRating;
use Edutiek\AssessmentService\Task\Data\CorrectorPoints;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Service implements InfoService
{
    public function __construct(
        private int $ass_id,
        private int $usr_id,
        private Repositories $repos,
        private LanguageService $lang,
    ) {
    }

    public function getInfos(int $task_id, int $writer_id, array $positions): array
    {
        $settings = $this->repos->correctionSettings()->one($this->ass_id);
        $with_points = $settings?->getEnablePartialPoints() ?? false;
        $with_ratings = $settings?->getEnableCommentRatings() ?? false;

        $infos = [];
        foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {

            if (in_array($assignment->getPosition(), $positions, true)) {
                $corrector_id = $assignment->getCorrectorId();

                $corrector_comments = $this->repos->correctorComment()->allByTaskIdAndWriterIdAndCorrectorId(
                    $task_id,
                    $writer_id,
                    $corrector_id
                );

                $corrector_points = $with_points ?
                    $this->repos->correctorPoints()->allByTaskIdAndWriterIdAndCorrectorId(
                        $task_id,
                        $writer_id,
                        $corrector_id
                    ) : [];

                foreach ($corrector_comments as $comment) {
                    $sum_of_points = 0;
                    foreach ($corrector_points as $points) {
                        if ($points->getCommentId() == $comment->getId()) {
                            $sum_of_points += $points->getPoints();
                        }
                    }

                    $rating_text = '';
                    if ($with_ratings && $comment->getRating() === CommentRating::EXCELLENT->value) {
                        $rating_text = $settings->getPositiveRating();
                    }
                    if ($with_ratings && $comment->getRating() === CommentRating::CARDINAL->value) {
                        $rating_text = $settings->getNegativeRating();
                    }

                    $infos[] = new CorrectorCommentInfo(
                        $comment,
                        $assignment->getPosition(),
                        $sum_of_points,
                        $rating_text,
                        $this->lang->txt($assignment->getPosition()->initialsLanguageVariable())
                    );
                }
            }
        }

        return $infos;
    }

    public function filterAndLabelInfos(array $infos, int $parent_no): array
    {
        $sort = [];
        foreach ($infos as $info) {
            if ($info->getComment()->getParentNumber() == $parent_no) {
                $key = sprintf('%06d', $info->getComment()->getStartPosition()) . $info->getComment()->getKey();
                $sort[$key] = $info;
            }
        }
        ksort($sort);

        $result = [];
        $number = 1;
        foreach ($sort as $info) {
            // only comments with details to show should get a label
            // others are only marks in the text
            $label = '';
            if ($info->hasDetailsToShow()) {
                $label = ($parent_no . '.' . $number++);
            }
            $result[] = $info->withLabel($label);
        }

        return $result;
    }
}
