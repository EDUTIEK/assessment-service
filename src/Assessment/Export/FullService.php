<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;

interface FullService
{
    /**
     * Download the written texts as PDFs
     * @param WritingTask[] $writings
     * @return bool true, if a background task has started
     */
    public function downloadWritings(array $writings, bool $anonymous): bool;

    /**
     * Download the corrected texts as PDFs
     * @param WritingTask[] $writings
     * @return bool true, if a background task has started
     */
    public function downloadCorrections(array $writings, bool $anonymous_writer, bool $anonymous_corrector): bool;
}
