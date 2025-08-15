<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class CorrectionSettings implements AssessmentEntity
{
    /** @var CorrectionSettingsError[] */
    private $validation_errors = [];

    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getRequiredCorrectors(): int;
    abstract public function setRequiredCorrectors(int $required_correctors): self;
    abstract public function getMaxAutoDistance(): float;
    abstract public function setMaxAutoDistance(float $max_auto_distance): self;
    abstract public function getMutualVisibility(): bool;
    abstract public function setMutualVisibility(bool $mutual_visibility): self;
    abstract public function getAssignMode(): AssignMode;
    abstract public function setAssignMode(AssignMode $assign_mode): self;
    abstract public function getStitchWhenDistance(): bool;
    abstract public function setStitchWhenDistance(bool $stitch_when_distance): self;
    abstract public function getStitchWhenDecimals(): bool;
    abstract public function setStitchWhenDecimals(bool $stitch_when_decimals): self;
    abstract public function getAnonymizeCorrectors(): bool;
    abstract public function setAnonymizeCorrectors(bool $anonymize_correctors): self;
    abstract public function getReportsEnabled(): bool;
    abstract public function setReportsEnabled(bool $reports_enabled): self;
    abstract public function getReportsAvailableStart(): ?DateTimeImmutable;
    abstract public function setReportsAvailableStart(?DateTimeImmutable $reports_available_start): self;

    public function isStitchPossible() : bool
    {
        return $this->getRequiredCorrectors() > 1 && ($this->getStitchWhenDecimals() || $this->getStitchWhenDistance());
    }

    public function addValidationError(CorrectionSettingsError $error)
    {
        $this->validation_errors[] = $error;
    }

    public function getValidationErrors(): array
    {
        return $this->validation_errors;
    }
}
