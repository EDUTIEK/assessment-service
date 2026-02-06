<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;

/**
 * Enhanced documentation of a corrector comment
 */
class CorrectorCommentInfo
{
    protected string $label = '';


    public function __construct(
        private readonly CorrectorComment $comment,
        private readonly GradingPosition $position,
        private readonly float $points,
        private readonly string $rating_text,
        private readonly string $position_text
    ) {
    }

    /**
     * @return CorrectorComment
     */
    public function getComment(): CorrectorComment
    {
        return $this->comment;
    }

    /**
     * Get the assignment position of the corrector
     */
    public function getPosition(): GradingPosition
    {
        return $this->position;
    }

    /**
     * Get the sum of points that should be shown
     */
    public function getPoints(): float
    {
        return $this->points;
    }

    /**
     * Get the rating text that should be shown
     */
    public function getRatingText(): string
    {
        return $this->rating_text;
    }

    /**
     * Get the text of the grading position
     */
    public function getPositionText(): string
    {
        return $this->position_text;
    }

    /**
     * Get a comment label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set a comment label
     * The label is built when all comemnts to be shown for a parent are known
     */
    public function withLabel(string $label): static
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * Get if details can be shown for the comment
     * Otherwise the marking does not need a label
     */
    public function hasDetailsToShow(): bool
    {
        return !empty($this->comment->getComment())
            || (!empty($this->getRatingText()))
            || (!empty($this->getPoints()));
    }

}
