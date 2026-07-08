<?php

namespace Edutiek\AssessmentService\System\HtmlProcessing;

use Edutiek\AssessmentService\System\Data\HeadlineScheme;

interface FullService
{
    /**
     * Fill a template with data
     */
    public function fillTemplate(string $template, array $data): string;

    /**
     * Remove any non-allowed tags and attributes from the content
     */
    public function secureContent(string $html): string;

    /**
     * Process HTML content (written text or instructions) for marking functions in the web apps
     * This will add the paragraph numbers and headline prefixes
     * and split up all text to single words embedded in <w-p> elements.
     * - the 'w' attribute is the word number
     * - the 'p' attribute is the paragraph number
     */
    public function getContentForMarking(
        string $html,
        bool $add_paragraph_numbers,
        HeadlineScheme $headline_scheme,
        ServiceVersion $service_version
    ): string;

    /**
     * Process HTML content (written text or instructions) for inclusion in a PDF file
     * - add the paragraph numbers and headline prefixes
     * - add the content style and style for paragraph numbers
     */
    public function getContentForPdf(
        string $html,
        bool $add_paragraph_numbers,
        HeadlineScheme $headline_scheme,
        ServiceVersion $service_version
    ): string;

    /**
     * Add the styles for pdf generation to the content
     */
    public function addContentStyles(string $html, bool $add_paragraph_numbers, HeadlineScheme $headline_scheme): string;

    /**
     * Add Styles for PDF generation of correction texts
     */
    public function addCorrectionStyles(string $html): string;

    /**
     * Get the XSLt Processor for an XSL file
     *
     * @param array<string, mixed> $custom_parameters  will be provided for the XSL file
     */
    public function processXslt(
        string $html,
        string $xslt_file,
        HeadlineScheme $headline_scheme,
        ServiceVersion $service_version,
        array $custom_parameters = []
    ): string;

    /**
     * Replace the special <w-p> elements added in getContentForMarking() with standard <span> elements
     */
    public function replaceCustomMarkup(string $html): string;

    /**
     * Remove the special <w-p> elements added in getContentForMarking()
     */
    public function removeCustomMarkup(string $html): string;
}
