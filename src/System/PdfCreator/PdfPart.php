<?php

namespace Edutiek\AssessmentService\System\PdfCreator;

/**
 * Part that has to be added to the Pdf
 * A part consists of one or more pdf pages and is filled with pdf elements
 * If the part contains long elements then it may create several real pages
 */
class PdfPart
{
    const ORIENTATION_PORTRAIT = 'portrait';
    const ORIENTATION_LANDSCASPE = 'landscape';

    const FORMAT_A4 = 'A4';
    const FORMAT_A5 = 'A5';
    
    private string $format;
    private string $orientation;

    private $print_header = true;
    private $print_footer = true;

    private $header_margin = 5;
    private $footer_margin = 10;

    private $top_margin = 20;
    private $bottom_margin = 10;
    private $left_margin = 15;
    private $right_margin = 15;


    private array $elements;

    /**
     * Constructor
     * @param string $format
     * @param string $orientation
     * @param PdfElement[]  $elements
     */
    public function __construct(
        string $format = self::FORMAT_A4, 
        string $orientation = self::ORIENTATION_PORTRAIT,
        array $elements = []
    ) 
    {
        $this->format = $format;
        $this->orientation = $orientation;
        $this->elements = $elements;
    }

    /**
     * Get the page format
     * @see FORMAT_A4, FORMAT_A5
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Get the page orientation
     * @see ORIENTATION_PORTRAIT, ORIENTATION_LANDSCASPE
     */
    public function getOrientation(): string
    {
        return $this->orientation;
    }

    /**
     * Get the elements of the page
     * @return PdfElement[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
    
    
    /**
     * Add an element to the Page
     * @param PdfElement $element
     * @return void
     */
    public function withElement(PdfElement $element) : self
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    /**
     * Get if the header should be printed
     * @return bool
     */
    public function getPrintHeader(): bool
    {
        return $this->print_header;
    }

    /**
     * Set if the header should be printed
     * @param bool $print_header
     * @return PdfPart
     */
    public function withPrintHeader(bool $print_header): PdfPart
    {
        $clone = clone $this;
        $this->print_header = $print_header;
        return $this;
    }

    /**
     * Get if the footer should be printed
     * @return bool
     */
    public function getPrintFooter(): bool
    {
        return $this->print_footer;
    }

    /**
     * Set if the footer should be printed
     * @param bool $print_footer
     * @return PdfPart
     */
    public function withPrintFooter(bool $print_footer): PdfPart
    {
        $clone = clone $this;
        $this->print_footer = $print_footer;
        return $this;
    }

    /**
     * Get the margin between page top and header (mm)
     * @return int
     */
    public function getHeaderMargin(): int
    {
        return $this->header_margin;
    }

    /**
     * Set the margin between page top and header (mm)
     * @param int $header_margin
     * @return PdfPart
     */
    public function withHeaderMargin(int $header_margin): PdfPart
    {
        $clone = clone $this;
        $clone->header_margin = $header_margin;
        return $clone;
    }

    /**
     * Get the margin between page bottom and footer (mm)
     * @return int
     */
    public function getFooterMargin(): int
    {
        return $this->footer_margin;
    }

    /**
     * Set the margin between page bottom and footer (mm)
     * @param int $footer_margin
     * @return PdfPart
     */
    public function withFooterMargin(int $footer_margin): PdfPart
    {
        $clone = clone $this;
        $clone->footer_margin = $footer_margin;
        return $clone;
    }

    /**
     *  Get the margin between page top and content (mm)
     * @return int
     */
    public function getTopMargin(): int
    {
        return $this->top_margin;
    }

    /**
     * Set the margin between page top and content (mm)
     * @param int $top_margin
     * @return PdfPart
     */
    public function withTopMargin(int $top_margin): PdfPart
    {
        $clone = clone $this;
        $clone->top_margin = $top_margin;
        return $clone;
    }

    /**
     * Get the margin between page bottom and content (mm)
     * @return int
     */
    public function getBottomMargin(): int
    {
        return $this->bottom_margin;
    }

    /**
     * Set the margin between page bottom and content (mm)
     * @param int $bottom_margin
     * @return PdfPart
     */
    public function withBottomMargin(int $bottom_margin): PdfPart
    {
        $clone = clone $this;
        $clone->bottom_margin = $bottom_margin;
        return $clone;
    }

    /**
     * Get the margin between page left side and content (mm)
     * @return int
     */
    public function getLeftMargin(): int
    {
        return $this->left_margin;
    }

    /**
     * Set the margin between page left side and content (mm)
     * @param int $left_margin
     * @return PdfPart
     */
    public function withLeftMargin(int $left_margin): PdfPart
    {
        $clone = clone $this;
        $clone->left_margin = $left_margin;
        return $clone;
    }

    /**
     * Get the margin between page right side and content (mm)
     * @return int
     */
    public function getRightMargin(): int
    {
        return $this->right_margin;
    }

    /**
     * Set the margin between page right side and content (mm)
     * @param int $right_margin
     * @return PdfPart
     */
    public function withRightMargin(int $right_margin): PdfPart
    {
        $clone = clone $this;
        $clone->right_margin = $right_margin;
        return $clone;
    }

    public function withFormat(string $format) : PdfPart
    {
        $clone = clone $this;
        $clone->format = $format;
        return $clone;
    }

    public function withOrientation(string $orientation) : PdfPart
    {
        $clone = clone $this;
        $clone->orientation = $orientation;
        return $clone;
    }
}