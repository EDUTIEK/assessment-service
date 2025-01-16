<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class CorrectorSummary implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): void;
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): void;
    public abstract function getSummaryText(): ?string;
    public abstract function setSummaryText(?string $summary_text): void;
    public abstract function getPoints(): ?float;
    public abstract function setPoints(?float $points): void;
    public abstract function getLastChange(): ?DateTimeImmutable;
    public abstract function setLastChange(?DateTimeImmutable $last_change): void;
    public abstract function getIncludeComments(): ?int;
    public abstract function setIncludeComments(?int $include_comments): void;
    public abstract function getIncludeCommentRatings(): ?int;
    public abstract function setIncludeCommentRatings(?int $include_comment_ratings): void;
    public abstract function getIncludeCommentPoints(): ?int;
    public abstract function setIncludeCommentPoints(?int $include_comment_points): void;
    public abstract function getIncludeCriteriaPoints(): ?int;
    public abstract function setIncludeCriteriaPoints(?int $include_criteria_points): void;
    public abstract function getCorectionAuthorized(): ?DateTimeImmutable;
    public abstract function setCorectionAuthorized(?DateTimeImmutable $corection_authorized): void;
    public abstract function getCorrectionAuthorizedBy(): ?int;
    public abstract function setCorrectionAuthorizedBy(?int $correction_authorized_by): void;
}
