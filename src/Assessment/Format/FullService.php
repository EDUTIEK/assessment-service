<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Format;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    public function resultAvailability(): string;
    public function finalResult(?Writer $writer): string;

    /**
     * Format the writing status of a writer
     * - Add the actor name if the writer is excluded or authorized by another person
     */
    public function writingStatus(Writer $writer): string;

    /**
     * Get the options to select a writing status for filtering, indexed by backed values
     * @return array<string, string>
     */
    public function writingStatusOptions(): array;

    /**
     * Format the combined status of a writer
     */
    public function combinedStatus(Writer $writer): ?string;

    /**
     * Get the options to select a combined status for filtering, indexed by backed values
     * @return array<string, string>
     */
    public function combinedStatusOptions(): array;
}
