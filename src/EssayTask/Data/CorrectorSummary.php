<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class CorrectorSummary implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): self;
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): self;
    public abstract function getSummaryText(): ?string;
    public abstract function setSummaryText(?string $summary_text): self;
    public abstract function getPoints(): ?float;
    public abstract function setPoints(?float $points): self;
    public abstract function getLastChange(): ?DateTimeImmutable;
    public abstract function setLastChange(?DateTimeImmutable $last_change): self;
    public abstract function getIncludeComments(): ?int;
    public abstract function setIncludeComments(?int $include_comments): self;
    public abstract function getIncludeCommentRatings(): ?int;
    public abstract function setIncludeCommentRatings(?int $include_comment_ratings): self;
    public abstract function getIncludeCommentPoints(): ?int;
    public abstract function setIncludeCommentPoints(?int $include_comment_points): self;
    public abstract function getIncludeCriteriaPoints(): ?int;
    public abstract function setIncludeCriteriaPoints(?int $include_criteria_points): self;
    public abstract function getCorectionAuthorized(): ?DateTimeImmutable;
    public abstract function setCorectionAuthorized(?DateTimeImmutable $corection_authorized): self;
    public abstract function getCorrectionAuthorizedBy(): ?int;
    public abstract function setCorrectionAuthorizedBy(?int $correction_authorized_by): self;
}
