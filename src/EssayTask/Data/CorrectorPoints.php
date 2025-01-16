<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorPoints implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getCommentId(): ?int;
    public abstract function setCommentId(?int $comment_id): void;
    public abstract function getCriterionId(): ?int;
    public abstract function setCriterionId(?int $criterion_id): void;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): void;
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): void;
    public abstract function getPoints(): float;
    public abstract function setPoints(float $points): void;
}
