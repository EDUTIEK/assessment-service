<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

final class UsageScope
{
    public function __construct(
        private readonly string $component,
        private readonly int $user_id,
        private readonly ?int $ass_id = null,
        private readonly ?int $task_id = null,
        private readonly ?int $writer_id = null,
        private readonly ?int $corrector_id = null,
        private readonly bool $user_id_used = false,
    ) {
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getAssId(): ?int
    {
        return $this->ass_id;
    }

    public function getTaskId(): ?int
    {
        return $this->task_id;
    }

    public function getWriterId(): ?int
    {
        return $this->writer_id;
    }

    public function getCorrectorId(): ?int
    {
        return $this->corrector_id;
    }

    public function isUserIdUsed(): bool
    {
        return $this->user_id_used;
    }

    public function isWriter(): bool
    {
        return $this->writer_id !== null;
    }

    public function isCorrector(): bool
    {
        return $this->corrector_id !== null;
    }
}