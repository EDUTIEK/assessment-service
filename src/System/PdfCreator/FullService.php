<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfCreator;

use Edutiek\AssessmentService\System\Data\ImageDescriptor;

interface FullService
{
    /**
     * Generate a pdf from an HTML text
     * Compliance with PDF/A-2B shall be achieved
     * @see https://de.wikipedia.org/wiki/PDF/A
     */
    public function createPdf(string $html, Options $options): string;
}
