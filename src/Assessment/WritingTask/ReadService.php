<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WritingTask;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;

interface ReadService
{
    /**
     * @return WritingTask[]
     */
    public function all(): array;

    /**
     * @return WritingTask[]
     */
    public function allByWriterIds(array $writer_ids): array;
}
