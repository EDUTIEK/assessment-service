<?php

namespace Edutiek\AssessmentService\Task\AssessmentStatus;

class CorrectorCorrectionSummary
{
    public function __construct(
        private int $corrector_id,
        private int $first_corrections,
        private int $second_corrections,
        private int $stitch_corrections,
        private int $authorized_corrections,
        private int $open_corrections
    ) {
    }

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

    public function getStitchCorrections(): int
    {
        return $this->stitch_corrections;
    }

    public function getAuthorizedCorrections(): int
    {
        return $this->authorized_corrections;
    }

    public function getOpenCorrections(): int
    {
        return $this->open_corrections;
    }
}
