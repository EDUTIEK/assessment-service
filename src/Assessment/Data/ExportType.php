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
}
