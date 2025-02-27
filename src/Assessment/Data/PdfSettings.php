<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use Edutiek\AssessmentService\System\PdfCreator\PdfSettings as PdfCreatorSettings;

abstract class PdfSettings implements AssessmentEntity, PdfCreatorSettings
{
    /**
     * Minimum margin on all sides of the pdf (mm)
     */
    private const MIN_MARGIN = 0;

    /**
     * Height of a header (mm)
     */
    private const HEADER_HEIGTH = 15;

    /**
     * Height of a footer (mm)
     */
    private const FOOTER_HEIGHT = 5;

    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getAddHeader(): bool;
    abstract public function setAddHeader(bool $add_header): self;
    abstract public function getAddFooter(): bool;
    abstract public function setAddFooter(bool $add_footer): self;
    abstract public function getTopMargin(): int;
    abstract public function setTopMargin(int $top_margin): self;
    abstract public function getBottomMargin(): int;
    abstract public function setBottomMargin(int $bottom_margin): self;
    abstract public function getLeftMargin(): int;
    abstract public function setLeftMargin(int $left_margin): self;
    abstract public function getRightMargin(): int;
    abstract public function setRightMargin(int $right_margin): self;


    public function getHeaderMargin(): int
    {
        return $this->getAddHeader() ? $this->getTopMargin() : 0;
    }

    public function getFooterMargin(): int
    {
        return $this->getAddFooter() ? $this->getBottomMargin() : 0;
    }

    public function getContentTopMargin(): int
    {
        return $this->getTopMargin() + ($this->getAddHeader() ? self::HEADER_HEIGTH : 0);
    }

    public function getContentBottomMargin(): int
    {
        return $this->getBottomMargin() + ($this->getAddFooter() ? self::FOOTER_HEIGHT : 0);
    }
}
