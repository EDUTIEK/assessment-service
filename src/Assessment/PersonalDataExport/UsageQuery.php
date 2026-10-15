<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

final class UsageQuery
{
    public function __construct(
        private readonly int $user_id,
        private readonly ?int $ass_id = null,
        private readonly ?int $task_id = null,
        private readonly ?int $writer_id = null,
        private readonly ?int $corrector_id = null,
        private readonly bool $user_id_used_upstream = false,
        /**
         * Aufrufkette der NÄCHSTEN Ebenen, als Stapel von Ebenen-Gruppen:
         *   steps[0] = UsageResolver[] der direkt nächsten Ebene
         *   steps[1] = UsageResolver[] der Ebene danach
         *   ...
         * Jede Komponente ruft steps[0] selbst auf und reicht
         * array_slice(steps, 1) an ihre Kinder weiter.
         *
         * @var array<int, array<int, UsageResolver>>
         */
        private readonly array $steps = [],
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getAssId(): ?int
    {
        return $this->ass_id;
    }

    public function getWriterId(): ?int
    {
        return $this->writer_id;
    }

    public function getCorrectorId(): ?int
    {
        return $this->corrector_id;
    }

    public function getTaskId(): ?int
    {
        return $this->task_id;
    }

    public function isUserIdUsedUpstream(): bool
    {
        return $this->user_id_used_upstream;
    }

    /**
     * @return UsageResolver[]
     */
    public function getNextSteps(): array
    {
        return $this->steps[0] ?? [];
    }

    public function nextQuery(
        ?int $ass_id = null,
        ?int $task_id = null,
        ?int $writer_id = null,
        ?int $corrector_id = null,
        bool $user_id_used_upstream = false
    ): self {
        return new self(
            $this->user_id,
            $ass_id ?? $this->ass_id,
            $task_id ?? $this->task_id,
            $writer_id ?? $this->writer_id,
            $corrector_id ?? $this->corrector_id,
            $user_id_used_upstream || $this->user_id_used_upstream,
            array_slice($this->steps, 1)
        );
    }
}
