<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

use Edutiek\AssessmentService\Assessment\Data\LogEntry;

interface FullService extends TasksService
{
    /**
     * Create the log as a CSV string
     */
    public function createCsv() : string;
    /** @return LogEntry[] */
    public function all(): array;
}