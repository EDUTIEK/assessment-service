<?php

namespace Edutiek\AssessmentService\Views\Data;

abstract class EssayTaskSummary
{
    abstract public function getLastSave(): ?\DateTimeImmutable;

    abstract public function hasPdfUploads(): bool;

    abstract public function getWords(): ?int;
}