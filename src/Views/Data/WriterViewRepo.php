<?php

namespace Edutiek\AssessmentService\Views\Data;

interface WriterViewRepo
{
    /**
     * @param array $filter
     * @param int   $limit
     * @param int   $offset
     * @return WriterView[]
     */
    public function some(array $filter, ?int $limit = null, ?int $offset = null): array;

    /**
     * Get the number of writers fulfilling the client status filter options
     *
     * @return array<string, int> client filter option value > count
     */
    public function clientFilterCounts(int $ass_id): array;
}
