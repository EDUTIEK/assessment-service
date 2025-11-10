<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectionSettings implements TaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getCriteriaMode(): CriteriaMode;
    abstract public function setCriteriaMode(CriteriaMode $criteria_mode): self;
    abstract public function getPositiveRating(): string;
    abstract public function setPositiveRating(string $positive_rating): self;
    abstract public function getNegativeRating(): string;
    abstract public function setNegativeRating(string $negative_rating): self;
    abstract public function getEnableComments(): bool;
    abstract public function setEnableComments(bool $enable_comments): self;
    abstract public function getEnableCommentRatings(): bool;
    abstract public function setEnableCommentRatings(bool $enable_comment_ratings): self;
    abstract public function getEnablePartialPoints(): bool;
    abstract public function setEnablePartialPoints(bool $enable_partial_points): self;
    abstract public function getEnableSummaryPdf(): bool;
    abstract public function setEnableSummaryPdf(bool $enable_summary_pdf): self;
    abstract public function getSummaryPdfAdvice(): ?string;
    abstract public function setSummaryPdfAdvice(?string $summary_pdf_advice): self;
}
