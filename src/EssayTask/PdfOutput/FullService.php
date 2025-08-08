<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfOutput;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;

interface FullService
{
    public function getWritingAsPdf(Essay $essay, bool $plainContent = false, bool $onlyText = false) : string;
    public function getPageImage(string $key): ?ImageDescriptor;
}
