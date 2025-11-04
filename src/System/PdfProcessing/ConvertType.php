<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

enum ConvertType: string
{
    case ONE = 'asOne';
    case ONE_PER_PAGE = 'asOnePerPage';
}
