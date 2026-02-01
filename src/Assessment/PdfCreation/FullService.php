<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;

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
    public function createWritingPdf(int $task_id, int $writer_id, bool $anonymous = false): string;

    /**
     * Create a ZIP file with all writing PDFs
     * It may consist of text writtens in the web app and/or uploaded pdf files
     * @param WritingTask[] $writings
     */
    public function createWritingZip(array $writings, bool $anonymous = false): string;

    /**
     * Create the PDF of a correction
     * It consists of parts that can be sorted and activated
     */
    public function createCorrectionPdf(int $task_id, int $writer_id, bool $anonymous_writer, bool $anonymous_corrector): string;

    /**
     * Create a PDF with the collected correction reports of all correctors
     */
    public function createCorrectionReport(int $ass_id): string;
}
