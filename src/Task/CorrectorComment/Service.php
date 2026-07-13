<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\EssayTask\Data\CommentRating;
use Edutiek\AssessmentService\EssayTask\Data\CorrectionMark;
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

                    $symbol = '';
                    $marks = CorrectionMark::multiFromArray((array) json_decode((string) $comment->getMarks()));
                    if (!empty($marks)) {
                        $mark = reset($marks);
                        $symbol = $mark->getSymbol();
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
                        $symbol,
                        $rating_text,
                        $this->lang->txt($assignment->getPosition()->initialsLanguageVariable())
                    );
                }
            }
        }

        return $infos;
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     * @param ?int $parent_no  number of the parent page or paragraph, or null to not filter the infos
     * @return CorrectorCommentInfo[]
     */
    public function filterAndLabelInfos(array $infos, ?int $parent_no): array
    {
        $sort = [];
        foreach ($infos as $info) {
            if ($parent_no === null || $info->getComment()->getParentNumber() == $parent_no) {
                $key = sprintf(
                    '%06d %06d %s',
                    $info->getComment()->getParentNumber(),
                    $info->getComment()->getStartPosition(),
                    $info->getComment()->getKey()
                );
                $sort[$key] = $info;
            }
        }
        ksort($sort);

        $result = [];
        $current_parent = null;
        $number = 1;
        foreach ($sort as $info) {
            $parent_no = $info->getComment()->getParentNumber();
            if ($parent_no !== $current_parent) {
                $current_parent = $parent_no;
                $number = 1;
            }

            // only comments with details to show should get a label
            // others are only marks in the text
            $label = '';

            if ($info->hasDetailsToShow()) {
                $label = ($parent_no . '.' . $number++);
                if ($info->getSymbol()) {
                    $label = $label . ' ' . $this->getSymbolForLabel($info->getSymbol());
                }
            }
            $result[] = $info->withLabel($label);

        }

        return $result;
    }


    public function getSymbolForLabel(string $symbol): string
    {
        switch ($symbol) {
            case CorrectionMark::SYMBOL_CHECK:
                return '√';
            case CorrectionMark::SYMBOL_CROSS:
                return '×';
            case CorrectionMark::SYMBOL_QUESTION:
                return '?';
            case CorrectionMark::SYMBOL_EXCLAMATION:
                return '!';
            case CorrectionMark::SYMBOL_MISSING:
                return '∀';
        }
        return '';
    }

    public function getSymbolText(string $symbol): string
    {
        switch ($symbol) {
            case CorrectionMark::SYMBOL_CHECK:
                return $this->lang->txt('symbol_check');
            case CorrectionMark::SYMBOL_CROSS:
                return $this->lang->txt('symbol_cross');
            case CorrectionMark::SYMBOL_QUESTION:
                return $this->lang->txt('symbol_question');
            case CorrectionMark::SYMBOL_EXCLAMATION:
                return $this->lang->txt('symbol_exclamation');
            case CorrectionMark::SYMBOL_MISSING:
                return $this->lang->txt('symbol_missing');
        }
        return '';
    }
}
