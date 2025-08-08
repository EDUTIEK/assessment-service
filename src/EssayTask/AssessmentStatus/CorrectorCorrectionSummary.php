<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

class CorrectorCorrectionSummary
{
    public function __construct(
        private int $corrector_id,
        private int $first_corrections,
        private int $second_corrections,
        private int $not_started,
        private int $authorized,
        private int $open_corrections
    ){}

    public function getCorrectorId(): int
    {
        return $this->corrector_id;
    }

    public function getFirstCorrections(): int
    {
        return $this->first_corrections;
    }

    public function getSecondCorrections(): int
    {
        return $this->second_corrections;
    }

    public function getNotStarted(): int
    {
        return $this->not_started;
    }

    public function getAuthorized(): int
    {
        return $this->authorized;
    }

    public function getOpenCorrections(): int
    {
        return $this->open_corrections;
    }
}