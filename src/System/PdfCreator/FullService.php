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
     *
     * @param PdfPart[] $parts      Parts of the PDF
     * @param string $creator       Name of the creator app, e.h. name of the LMS
     * @param string $author        Name of the author, e.g. user creating the PDF
     * @param string $title         will be shown bold as first line in header
     * @param string $subject       will be shown as second line in header
     * @param string $keywords
     * @return string
     */
    public function createPdf(
        array $parts,
        string $creator = "",
        string $author = "",
        string $title = "",
        string $subject = "",
        string $keywords = ""
    ) : string;

    /**
     * Get a standard pdf part
     * It may consist of several elements and span over several pages
     *
     * @param PdfElement[] $elements
     */
    public function createStandardPart(
        array $elements = [],
        ?PdfSettings $pdf_settings = null
    ): PdfPart;

    /**
     * Get the path of an image for pdf processing
     */
    public function getImagePathForPdf(?ImageDescriptor $image): string;
}