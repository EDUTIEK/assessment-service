<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorPrefs implements TaskEntity
{
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getEssayPageZoom(): float;
    abstract public function setEssayPageZoom(float $essay_page_zoom): self;
    abstract public function getEssayTextZoom(): float;
    abstract public function setEssayTextZoom(float $essay_text_zoom): self;
    abstract public function getSummaryTextZoom(): float;
    abstract public function setSummaryTextZoom(float $summary_text_zoom): self;
}
