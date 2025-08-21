<?php

namespace Edutiek\AssessmentService\Task\AssessmentStatus;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    public function hasComments();
    public function hasAuthorizedSummaries(?int $corrector_id = null);
    /** @return CorrectionStatus[] */
    public function allWriterCorrectionStatus() : array;
    public function oneWriterCorrectionStatus(Writer $writer) : CorrectionStatus;

    /**
     * @param int[]|null $corrector_ids
     * @return CorrectorCorrectionSummary[]
     */
    public function allCorrectorCorrectionSummaries(?array $corrector_ids = null) : array;
    public function oneCorrectorCorrectionSummary(int $corrector_id): CorrectorCorrectionSummary;

    public function getCorrectorsWithOpenAuthorizations();
}
