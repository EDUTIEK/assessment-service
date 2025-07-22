<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

interface FullService
{
    public function hasComments();
    public function hasAuthorizedSummaries(?int $corrector_id = null);
    /** @return WriterEssayStatus[] */
    public function allWriterEssayStatus(): array;
    public function oneWriterEssayStatus(int $writer_id): ?WriterEssayStatus;
}
