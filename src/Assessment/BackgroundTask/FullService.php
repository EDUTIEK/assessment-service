<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\BackgroundTask;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;

interface FullService
{
    /**
     * @param WritingTask[] $writings
     */
    public function downloadWritings(array $writings, bool $anonymous, string $filename): void;

    /**
     * @param WritingTask[] $writings
     */
    public function downloadCorrections(array $writings, bool $anonymous_writer, bool $anonymous_corrector, string $filename): void;
}
