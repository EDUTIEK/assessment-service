<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Edutiek\AssessmentService\System\PdfCreator\Options;
use Generator;

interface FullService
{
    public function create(string $html, Options $options): string;

    /**
     * Join separate PDF files into one
     * @param string[] $pdf_ids
     * @return string
     */
    public function join(array $pdf_ids): string;

    /**
     * Create a copy of a PDF file
     */
    public function copy(string $pdf_id): string;

    /**
     * Count the pages of a pdf file
     */
    public function count(string $pdf_id): int;

    /**
     * Print two pdf files on top of each other
     */
    public function onTopOfEachOther(string $pdf_left, string $pdf_right): string;

    /**
     * Reset the List of saved files
     * This should be called at top level of a staged processing before the first step
     */
    public function resetSavedFiles(): void;

    /**
     * Cleanup saved files except the ones specified
     * This should be called at top level of a staged processing after the last step
     *
     * @param string[] $keep_ids    file ids of files that should be kept
     */
    public function cleanupSavedFiledExcept(array $keep_ids): void;
}
