<?php

namespace Edutiek\AssessmentService\Task\Format;

use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;

interface FullService
{
    /**
     * Get the display value of a correction result
     * - Respect the correction setting to hide a status before authorization
     * - Never show a result to others before authorization
     * - Show the revised status based on the procedure setting
     */
    public function correctionResult(?CorrectorSummary $summary, bool $is_own): string;

    /**
     * Get the display value of a grading status
     * - Respect the correction setting to hide a status before authorization
     * - Show the revised status based on the procedure setting
     */
    public function gradingStatus(?GradingStatus $status, $is_own): string;

    /**
     * Get the available options for a grading status indexed by backed status valued
     * @return array<string, string>
     */
    public function gradingStatusOptions(): array;

    /**
     * Get the available options for a grading position, indexed by backt position values
     * @return array<int, string>
     */
    public function gradingPositionOptions(): array;
}
