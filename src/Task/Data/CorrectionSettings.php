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
}
