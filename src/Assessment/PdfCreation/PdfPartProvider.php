<?php

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

interface PdfPartProvider
{
    /**
     * Get the parts provided for the pdf creation
     * @return PdfConfigPart[]
     */
    public function getAvailableParts(): array;

    /**
     * @param string $key
     * @return string id of a saved pdf file
     */
    public function renderPart(string $key): string;
}
