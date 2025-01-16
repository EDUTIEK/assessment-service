<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class CorrectorSetting implements AssessmentEntity
{
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
    public abstract function getRequiredCorrectors(): int;
    public abstract function setRequiredCorrectors(int $required_correctors): void;
    public abstract function getMaxAutoDistance(): float;
    public abstract function setMaxAutoDistance(float $max_auto_distance): void;
    public abstract function getMutualVisibility(): int;
    public abstract function setMutualVisibility(int $mutual_visibility): void;
    public abstract function getAssignMode(): string;
    public abstract function setAssignMode(string $assign_mode): void;
    public abstract function getStitchWhenDistance(): int;
    public abstract function setStitchWhenDistance(int $stitch_when_distance): void;
    public abstract function getStitchWhenDecimals(): int;
    public abstract function setStitchWhenDecimals(int $stitch_when_decimals): void;
    public abstract function getAnonymizeCorrectors(): int;
    public abstract function setAnonymizeCorrectors(int $anonymize_correctors): void;
    public abstract function getReportsEnabled(): int;
    public abstract function setReportsEnabled(int $reports_enabled): void;
    public abstract function getReportsAvailableStart(): ?DateTimeImmutable;
    public abstract function setReportsAvailableStart(?DateTimeImmutable $reports_available_start): void;
}
