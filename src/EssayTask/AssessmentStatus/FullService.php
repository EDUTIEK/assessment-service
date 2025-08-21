<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    /** @return WriterEssaySummary[] */
    public function allWriterEssaySummaries(): array;
    public function oneWriterEssaySummary(int $writer_id): ?WriterEssaySummary;
}
