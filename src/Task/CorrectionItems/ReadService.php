<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectionItems;

use Edutiek\AssessmentService\Task\Data\CorrectionItem;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;
use ILIAS\Plugin\LongEssayAssessment\Task\Data\Settings;

interface ReadService
{
    /**
     * Get the correction items that are assigned to the user as corrector
     * @return CorrectionItem[]
     */
    public function getItems(bool $filtered): array;

    /**
     * Build a correction item from an assignment
     */
    public function buildCorrectionItem(CorrectorAssignment $assignment): CorrectionItem;

    /**
     * Get the data for display of the correction item to a corrector
     * @param CorrectionItem $item
     * @return string[]
     */
    public function getDisplayData(CorrectionItem $item): array;

    /**
     * Get the data for display of the correction item to an admin
     * @param CorrectionItem $item
     * @return string[]
     */
    public function getAdminData(Writer $writer, Settings $task): array;
}
