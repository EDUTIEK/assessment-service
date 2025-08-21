<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectionSettings implements EssayTaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getCriteriaMode(): CriteriaMode;
    abstract public function setCriteriaMode(CriteriaMode $criteria_mode): self;
    abstract public function getPositiveRating(): string;
    abstract public function setPositiveRating(string $positive_rating): self;
    abstract public function getNegativeRating(): string;
    abstract public function setNegativeRating(string $negative_rating): self;
    abstract public function getFixedInclusions(): bool;
    abstract public function setFixedInclusions(bool $fixed_inclusions): self;
    abstract public function getIncludeComments(): SummaryInclusion;
    abstract public function setIncludeComments(SummaryInclusion $include_comments): self;
    abstract public function getIncludeCommentRatings(): SummaryInclusion;
    abstract public function setIncludeCommentRatings(SummaryInclusion $include_comment_ratings): self;
    abstract public function getIncludeCommentPoints(): SummaryInclusion;
    abstract public function setIncludeCommentPoints(SummaryInclusion $include_comment_points): self;
    abstract public function getIncludeCriteriaPoints(): SummaryInclusion;
    abstract public function setIncludeCriteriaPoints(SummaryInclusion $include_criteria_points): self;
}
