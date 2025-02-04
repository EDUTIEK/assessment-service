<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectionSettings implements EssayTaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getCriteriaMode(): string;
    abstract public function setCriteriaMode(string $criteria_mode): self;
    abstract public function getPositiveRating(): string;
    abstract public function setPositiveRating(string $positive_rating): self;
    abstract public function getNegativeRating(): string;
    abstract public function setNegativeRating(string $negative_rating): self;
    abstract public function getFixedInclusions(): int;
    abstract public function setFixedInclusions(int $fixed_inclusions): self;
    abstract public function getIncludeComments(): int;
    abstract public function setIncludeComments(int $include_comments): self;
    abstract public function getIncludeCommentRatings(): int;
    abstract public function setIncludeCommentRatings(int $include_comment_ratings): self;
    abstract public function getIncludeCommentPoints(): int;
    abstract public function setIncludeCommentPoints(int $include_comment_points): self;
    abstract public function getIncludeCriteriaPoints(): int;
    abstract public function setIncludeCriteriaPoints(int $include_criteria_points): self;
}
