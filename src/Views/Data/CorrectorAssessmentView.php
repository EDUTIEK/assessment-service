<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Data;

use ILIAS\UI\Component\Input\Field\DateTime;
use DateTimeImmutable;

abstract readonly class CorrectorAssessmentView
{
    abstract public function getAssId(): int;
    abstract public function getContextId(): int;
    abstract public function getParentTitle(): string;
    abstract public function getTitle(): string;
    abstract public function getCorrectionStart(): ?DateTimeImmutable;
    abstract public function getCorrectionEnd(): ?DateTimeImmutable;
    abstract public function getAssignedGradings(): int;
    abstract public function getFinishedGradings(): int;
}
