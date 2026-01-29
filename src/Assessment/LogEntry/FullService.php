<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

use Edutiek\AssessmentService\Assessment\Data\LogEntry;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;

interface FullService extends TasksService
{
    /** @return LogEntry[] */
    public function all(): array;

    /**
     * Export the log to a file
     * @return string file_id of the exported file
     */
    public function export(ExportType $type): string;
}
