<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\PdfSettings;

use Edutiek\AssessmentService\Assessment\Data\PdfSettings;

interface FullService
{
    public function get(): PdfSettings;
    public function save(PdfSettings $settings): void;
}