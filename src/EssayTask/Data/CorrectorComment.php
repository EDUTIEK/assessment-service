<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorComment implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getEssayId(): int;
    abstract public function setEssayId(int $essay_id): self;
    abstract public function getComment(): ?string;
    abstract public function setComment(?string $comment): self;
    abstract public function getStartPosition(): int;
    abstract public function setStartPosition(int $start_position): self;
    abstract public function getEndPosition(): int;
    abstract public function setEndPosition(int $end_position): self;
    abstract public function getRating(): string;
    abstract public function setRating(string $rating): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getParentNumber(): int;
    abstract public function setParentNumber(int $parent_number): self;
    abstract public function getMarks(): ?string;
    abstract public function setMarks(?string $marks): self;
}
