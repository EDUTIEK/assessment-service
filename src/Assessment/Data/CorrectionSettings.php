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
    abstract public function getNoManualDecimals(): bool;
    abstract public function setNoManualDecimals(bool $no_manual_decimals): self;
    abstract public function getMaxAutoDistance(): float;
    abstract public function setMaxAutoDistance(float $max_auto_distance): self;
    abstract public function getMutualVisibility(): bool;
    abstract public function setMutualVisibility(bool $mutual_visibility): self;
    abstract public function getWaitForFirst(): bool;
    abstract public function setWaitForFirst(bool $wait_for_first): self;
    abstract public function getAssignMode(): AssignMode;
    abstract public function setAssignMode(AssignMode $assign_mode): self;
    abstract public function getProcedureWhenDistance(): bool;
    abstract public function setProcedureWhenDistance(bool $procedure_when_distance): self;
    abstract public function getProcedureWhenDecimals(): bool;
    abstract public function setProcedureWhenDecimals(bool $procedure_when_decimals): self;
    abstract public function getProcedure(): CorrectionProcedure;
    abstract public function setProcedure(CorrectionProcedure $procedure): self;
    abstract public function getApproximation(): CorrectionApproximation;
    abstract public function setApproximation(CorrectionApproximation $approximation): self;
    abstract public function getRevisionBetween(): bool;
    abstract public function setRevisionBetween(bool $revision_between): self;
    abstract public function getStitchAfterProcedure(): bool;
    abstract public function setStitchAfterProcedure(bool $stitch_after_procedure): self;
    abstract public function getUndoAuthorization(): bool;
    abstract public function setUndoAuthorization(bool $undo_authorization): self;
    abstract public function getInstantStatus(): bool;
    abstract public function setInstantStatus(bool $instant_status): self;
    abstract public function getPseudonymization(): Pseudonymization;
    abstract public function setPseudonymization(Pseudonymization $pseudonymization): self;
    abstract public function getAnonymizeCorrectors(): bool;
    abstract public function setAnonymizeCorrectors(bool $anonymize_correctors): self;
    abstract public function getReportsEnabled(): bool;
    abstract public function setReportsEnabled(bool $reports_enabled): self;
    abstract public function getReportsAvailableStart(): ?DateTimeImmutable;
    abstract public function setReportsAvailableStart(?DateTimeImmutable $reports_available_start): self;
    abstract public function getMaxPoints(): ?int;
    abstract public function setMaxPoints(?int $max_points): self;
    abstract public function getDownloadWriting(): bool;
    abstract public function setDownloadWriting(bool $download_writing): self;
    abstract public function getDownloadCorrection(): bool;
    abstract public function setDownloadCorrection(bool $download_correction): self;

    public function hasMultipleCorrectors(): bool
    {
        return $this->getRequiredCorrectors() > 1;
    }

    public function isStitchPossible(): bool
    {
        return $this->getRequiredCorrectors() > 1 && ($this->getProcedureWhenDecimals() || $this->getProcedureWhenDistance());
    }
}
