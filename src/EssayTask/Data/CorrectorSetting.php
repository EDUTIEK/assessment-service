<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorSetting implements EssayTaskEntity
{
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
    public abstract function getCriteriaMode(): string;
    public abstract function setCriteriaMode(string $criteria_mode): void;
    public abstract function getPositiveRating(): string;
    public abstract function setPositiveRating(string $positive_rating): void;
    public abstract function getNegativeRating(): string;
    public abstract function setNegativeRating(string $negative_rating): void;
    public abstract function getFixedInclusions(): int;
    public abstract function setFixedInclusions(int $fixed_inclusions): void;
    public abstract function getIncludeComments(): int;
    public abstract function setIncludeComments(int $include_comments): void;
    public abstract function getIncludeCommentRatings(): int;
    public abstract function setIncludeCommentRatings(int $include_comment_ratings): void;
    public abstract function getIncludeCommentPoints(): int;
    public abstract function setIncludeCommentPoints(int $include_comment_points): void;
    public abstract function getIncludeCriteriaPoints(): int;
    public abstract function setIncludeCriteriaPoints(int $include_criteria_points): void;
}
