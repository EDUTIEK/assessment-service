<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class CorrectorSummary implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getEssayId(): int;
    abstract public function setEssayId(int $essay_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getSummaryText(): ?string;
    abstract public function setSummaryText(?string $summary_text): self;
    abstract public function getPoints(): ?float;
    abstract public function setPoints(?float $points): self;
    abstract public function getLastChange(): ?DateTimeImmutable;
    abstract public function setLastChange(?DateTimeImmutable $last_change): self;
    abstract public function getIncludeComments(): ?int;
    abstract public function setIncludeComments(?int $include_comments): self;
    abstract public function getIncludeCommentRatings(): ?int;
    abstract public function setIncludeCommentRatings(?int $include_comment_ratings): self;
    abstract public function getIncludeCommentPoints(): ?int;
    abstract public function setIncludeCommentPoints(?int $include_comment_points): self;
    abstract public function getIncludeCriteriaPoints(): ?int;
    abstract public function setIncludeCriteriaPoints(?int $include_criteria_points): self;
    abstract public function getCorrectionAuthorized(): ?DateTimeImmutable;
    abstract public function setCorrectionAuthorized(?DateTimeImmutable $corection_authorized): self;
    abstract public function getCorrectionAuthorizedBy(): ?int;
    abstract public function setCorrectionAuthorizedBy(?int $correction_authorized_by): self;
}
