<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfInput;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\System\ConstraintHandling\Result;

interface FullService
{
    public function checkReplacePdf(Essay $essay): Result;
    public function replacePdf(Essay $essay, string $file_id): void;
    public function checkDeletePdf(Essay $essay): Result;
    public function deletePdf(Essay $essay): void;
}
