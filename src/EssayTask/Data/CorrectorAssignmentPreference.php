<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorAssignmentPreference implements EssayTaskEntity
{
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): self;
    public abstract function getEssayPageZoom(): float;
    public abstract function setEssayPageZoom(float $essay_page_zoom): self;
    public abstract function getEssayTextZoom(): float;
    public abstract function setEssayTextZoom(float $essay_text_zoom): self;
    public abstract function getSummaryTextZoom(): float;
    public abstract function setSummaryTextZoom(float $summary_text_zoom): self;
    public abstract function getIncludeComments(): int;
    public abstract function setIncludeComments(int $include_comments): self;
    public abstract function getIncludeCommentRatings(): int;
    public abstract function setIncludeCommentRatings(int $include_comment_ratings): self;
    public abstract function getIncludeCommentPoints(): int;
    public abstract function setIncludeCommentPoints(int $include_comment_points): self;
    public abstract function getIncludeCriteriaPoints(): int;
    public abstract function setIncludeCriteriaPoints(int $include_criteria_points): self;
}
