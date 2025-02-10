<?php

namespace Edutiek\AssessmentService\System\PdfCreator;

class PdfHtml extends PdfElement
{
    private string $html;

    /**
     * Constructor
     * @param $html - html code of the element
     * @see PdfElement
     */
    public function __construct(string $html, ?float $left = null, ?float $top = null, ?float $width = null, ?float $height = null)
    {
        parent::__construct($left, $top, $width, $height);

        $this->html = $html;
    }

    /**
     * Get the html code
     */
    public function getHtml() : string
    {
        return $this->html;
    }
}