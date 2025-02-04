<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorPrefs implements EssayTaskEntity
{
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getEssayPageZoom(): float;
    abstract public function setEssayPageZoom(float $essay_page_zoom): self;
    abstract public function getEssayTextZoom(): float;
    abstract public function setEssayTextZoom(float $essay_text_zoom): self;
    abstract public function getSummaryTextZoom(): float;
    abstract public function setSummaryTextZoom(float $summary_text_zoom): self;
    abstract public function getIncludeComments(): int;
    abstract public function setIncludeComments(int $include_comments): self;
    abstract public function getIncludeCommentRatings(): int;
    abstract public function setIncludeCommentRatings(int $include_comment_ratings): self;
    abstract public function getIncludeCommentPoints(): int;
    abstract public function setIncludeCommentPoints(int $include_comment_points): self;
    abstract public function getIncludeCriteriaPoints(): int;
    abstract public function setIncludeCriteriaPoints(int $include_criteria_points): self;
}
