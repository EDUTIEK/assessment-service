<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSnippets;

use Edutiek\AssessmentService\Task\Data\CorrectorSnippetPurpose;
use Edutiek\AssessmentService\System\Data\FileInfo;

interface FullService
{
    public function export(int $corrector_id, CorrectorSnippetPurpose $purpose): FileInfo;
}
