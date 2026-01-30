<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;

interface FullService
{
    /**
     * Download the written texts or PDFs
     * @param WritingTask[] $writings
     */
    public function downloadWritings(array $writings, bool $anonymous): void;
}
