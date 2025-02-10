<?php

namespace Edutiek\AssessmentService\System\PdfCreator;

abstract class PdfElement
{
    private ?float $left;
    private ?float $top;
    private ?float $width;
    private ?float $height;

    /**
     * 
     * @param float|null $left  - x position in mm
     * @param float|null $top - y position in mm
     * @param float|null $width - width in mm
     * @param float|null $height - height in mm
     */
    public function __construct(?float $left = null, ?float $top = null, ?float $width = null, ?float $height = null) 
    {
        $this->left = $left;
        $this->top = $top;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * left position in mm
     */
    public function getLeft(): ?float
    {
        return $this->left;
    }

    /**
     * Top position in mm
     */
    public function getTop(): ?float
    {
        return $this->top;
    }

    /**
     * Width in mm
     */
    public function getWidth(): ?float
    {
        return $this->width;
    }

    /**
     * Height in mm
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

}