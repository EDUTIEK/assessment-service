<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

class WriterEssayStatus
{
    public function __construct(
        private int $writer_id,
        private ?\DateTimeImmutable $last_save,
        private bool $has_pdf_uploads
    ){}

    public function getWriterId(): int
    {
        return $this->writer_id;
    }

    public function getLastSave(): \DateTimeImmutable
    {
        return $this->last_save;
    }

    public function hasPdfUploads(): bool
    {
        return $this->has_pdf_uploads;
    }
}