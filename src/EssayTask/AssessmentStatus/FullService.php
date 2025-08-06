<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    public function hasComments();
    public function hasAuthorizedSummaries(?int $corrector_id = null);
    /** @return WriterEssaySummary[] */
    public function allWriterEssaySummaries(): array;
    public function oneWriterEssaySummary(int $writer_id): ?WriterEssaySummary;
    /** @return CorrectionStatus[] */
    public function allWriterCorrectionStatus() : array;
    public function oneWriterCorrectionStatus(Writer $writer) : CorrectionStatus;
}
