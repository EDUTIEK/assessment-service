<?php

namespace Edutiek\AssessmentService\Views\Data;

use Edutiek\AssessmentService\Assessment\Data\Location;
use Edutiek\AssessmentService\Task\Data\Settings;

interface CorrectionsViewRepo
{
    /**
     * @param array $filter
     * @param int   $limit
     * @param int   $offset
     * @return CorrectionsView[]
     */
    public function some(array $filter, ?int $limit = null, ?int $offset = null): array;

    /**
     * @param array $filter
     * @return int
     */
    public function count(array $filter): int;

    /**
     * @param int[] $ass_ids
     * @return Location[]
     */
    public function locations(array $ass_ids): array;

    /**
     * @param array $ass_ids
     * @return Settings[]
     */
    public function tasks(array $ass_ids): array;

    /**
     * @param array $ass_ids
     * @return string[]
     */
    public function assessments(array $ass_ids): array;

    /**
     * @param int[] $ass_ids
     * @return bool
     */
    public function hasMultiTasks(array $ass_ids): bool;

    /**
     * @param int[] $ass_ids
     * @return int
     */
    public function visibleCorrectors(array $ass_ids): int;
}
