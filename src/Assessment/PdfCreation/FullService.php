<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

interface FullService
{
    /**
     * Get the parts for PDF generation, sorted by their position
     * @return PdfConfigPart[]
     */
    public function getSortedParts(PdfPurpose $purpose): array;

    /**
     * Save the position and activation of the parts for PDF generation
     * The values are taken from the part's property, not from the array index
     *
     * @param PdfConfigPart[] $parts
     */
    public function saveSortedParts(PdfPurpose $purpose, array $parts): void;


    /**
     * Create the PDF of a writer submission
     * It may consist of text written in the web app and/or an uploaded pdf file
     */
    public function createWritingPdf(int $writer_id): string;

    /**
     * Create the PDF of a correction
     * It consists of parts that can be sorted and activated
     */
    public function createCorrectionPdf(int $writer_id): string;
}
