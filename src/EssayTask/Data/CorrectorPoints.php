<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorPoints implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getEssayId(): int;
    abstract public function setEssayId(int $essay_id): self;
    abstract public function getCommentId(): ?int;
    abstract public function setCommentId(?int $comment_id): self;
    abstract public function getCriterionId(): ?int;
    abstract public function setCriterionId(?int $criterion_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getPoints(): float;
    abstract public function setPoints(float $points): self;
}
