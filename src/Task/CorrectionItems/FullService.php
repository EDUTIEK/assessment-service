<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectionItems;

use Edutiek\AssessmentService\Task\Data\CorrectionItem;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;
use ILIAS\Plugin\LongEssayAssessment\Task\Data\Settings;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;

/**
 * Service handling the correction items that are presented to a corrector
 */
interface FullService
{
    /**
     * Get the correction items that are assigned to the user as corrector
     * @param int[] $assignment_ids
     * @return CorrectionItem[]
     */
    public function getItems(bool $filtered, ?array $assignment_ids = null): array;

    /**
     * Build a correction item from an assignment
     */
    public function buildCorrectionItem(CorrectorAssignment $assignment): CorrectionItem;

    /**
     * Get the item data for a corrector
     * @return string[]
     */
    public function getCorrectorData(CorrectionItem $item): array;

    /**
     * Get the item data for an admin
     * @return string[]
     */
    public function getAdminData(Writer $writer, Settings $task): array;

    /**
     * Get the options that can be selected for a filter
     * @return array key => [ value => label ]
     */
    public function getFilterOptions(): array;

    /**
     * Get the current correction filter of the corrector
     * @return array key => [ value, ... ]
     */
    public function getFilterValues(): array;

    /**
     * Save a filter for showing assignments to the corrector
     * @param array $filter_values key => key => [ value, ... ]
     */
    public function saveFilterValues(array $filter_values);
}
