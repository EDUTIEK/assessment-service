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

    abstract public function getFormat(): PdfFormat;
    abstract public function setFormat(PdfFormat $format): self;

    abstract public function getFeedbackMode(): PdfFeedbackMode;
    abstract public function setFeedbackMode(PdfFeedbackMode $mode): self;

    public function getAddHeader(): bool
    {
        return true;
    }

    public function getAddFooter(): bool
    {
        return true;
    }

    public function getTopMargin(): int
    {
        return 10;
    }

    public function getBottomMargin(): int
    {
        return 10;
    }

    public function getLeftMargin(): int
    {
        return 10;
    }
    public function getRightMargin(): int
    {
        return 10;
    }

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
