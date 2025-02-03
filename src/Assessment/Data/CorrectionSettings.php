<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class CorrectionSettings implements AssessmentEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getRequiredCorrectors(): int;
    abstract public function setRequiredCorrectors(int $required_correctors): self;
    abstract public function getMaxAutoDistance(): float;
    abstract public function setMaxAutoDistance(float $max_auto_distance): self;
    abstract public function getMutualVisibility(): int;
    abstract public function setMutualVisibility(int $mutual_visibility): self;
    abstract public function getAssignMode(): string;
    abstract public function setAssignMode(string $assign_mode): self;
    abstract public function getStitchWhenDistance(): int;
    abstract public function setStitchWhenDistance(int $stitch_when_distance): self;
    abstract public function getStitchWhenDecimals(): int;
    abstract public function setStitchWhenDecimals(int $stitch_when_decimals): self;
    abstract public function getAnonymizeCorrectors(): int;
    abstract public function setAnonymizeCorrectors(int $anonymize_correctors): self;
    abstract public function getReportsEnabled(): int;
    abstract public function setReportsEnabled(int $reports_enabled): self;
    abstract public function getReportsAvailableStart(): ?DateTimeImmutable;
    abstract public function setReportsAvailableStart(?DateTimeImmutable $reports_available_start): self;
}
