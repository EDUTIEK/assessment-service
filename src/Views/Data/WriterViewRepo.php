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
}