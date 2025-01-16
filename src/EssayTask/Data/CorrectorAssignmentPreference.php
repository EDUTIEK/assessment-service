<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorAssignmentPreference implements EssayTaskEntity
{
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): void;
    public abstract function getEssayPageZoom(): float;
    public abstract function setEssayPageZoom(float $essay_page_zoom): void;
    public abstract function getEssayTextZoom(): float;
    public abstract function setEssayTextZoom(float $essay_text_zoom): void;
    public abstract function getSummaryTextZoom(): float;
    public abstract function setSummaryTextZoom(float $summary_text_zoom): void;
    public abstract function getIncludeComments(): int;
    public abstract function setIncludeComments(int $include_comments): void;
    public abstract function getIncludeCommentRatings(): int;
    public abstract function setIncludeCommentRatings(int $include_comment_ratings): void;
    public abstract function getIncludeCommentPoints(): int;
    public abstract function setIncludeCommentPoints(int $include_comment_points): void;
    public abstract function getIncludeCriteriaPoints(): int;
    public abstract function setIncludeCriteriaPoints(int $include_criteria_points): void;
}
