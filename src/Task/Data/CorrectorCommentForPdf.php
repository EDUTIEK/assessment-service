<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorCommentForPdf extends CorrectorComment
{
    // not saved
    protected string $label = '';
    protected bool $show_rating = true;
    protected bool $show_points = true;
    protected int $points = 0;

    /**
     * Get the points that are assigned for showing
     */
    public function getPoints(): int
    {
        return $this->points;
    }
    public function withPoints(int $points): static
    {
        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }

    /**
     * Get a comment label
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    public function withLabel(string $label): static
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * Get if rating should be shown
     */
    public function showRating(): bool
    {
        return $this->show_rating;
    }
    public function withShowRating(bool $show_rating): static
    {
        $clone = clone $this;
        $clone->show_rating = $show_rating;
        return $clone;
    }

    /**
     * Get if points should be shown
     */
    public function showPoints(): bool
    {
        return $this->show_points;
    }
    public function withShowPoints(bool $show_points): static
    {
        $clone = clone $this;
        $clone->show_points = $show_points;
        return $clone;
    }

    /**
     * Get if details should be shown
     */
    public function hasDetailsToShow(): bool
    {
        return !empty($this->getComment())
            || (!empty($this->getRating()) && $this->showPoints())
            || (!empty($this->getPoints()) && $this->showPoints());
    }
}
