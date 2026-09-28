<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum ExportType: string
{
    case RESULTS = 'results';
    case DOCUMENTATION = 'documentation';
    case REPORTS = 'reports';
    case LOG = 'log';
    case HASHES = 'hashes';

    case INSTRUCTION = 'instruction';
    case SOLUTION = 'solution';
    case WRITING = 'writing';
    case WRITINGS = 'writings';
    case CORRECTION = 'correction';
    case CORRECTIONS = 'corrections';
    case CORRECTORS = 'correctors';
    case ASSIGNMENTS = 'assignments';
    case WRITING_STATISTICS = 'writing_statistics';
    case CORRECTION_STATISTICS = 'correction_statistics';

    public function langVar()
    {
        return 'export_type_' . $this->value;
    }
}
