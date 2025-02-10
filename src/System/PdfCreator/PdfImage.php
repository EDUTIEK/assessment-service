<?php

namespace Edutiek\AssessmentService\System\PdfCreator;

class PdfImage extends PdfElement
{
    private string $path;

    /**
     * Constructor
     * @param $path - path of the image file (relative to the executing script)
     * @see PdfElement
     */
    public function __construct(string $path, ?float $left = null, ?float $top = null, ?float $width = null, ?float $height = null) 
    {
        parent::__construct($left, $top, $width, $height);

        $this->path = $path;
    }

    /**
     * Get the path of the image file, relative to the executing script
     */
    public function getPath() : string
    {
        return $this->path;
    }
}