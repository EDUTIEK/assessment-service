<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use Edutiek\AssessmentService\System\PdfCreator\PdfSettings as PdfCreatorSettings;
use Edutiek\AssessmentService\System\PdfCreator\Options;

abstract class PdfSettings implements AssessmentEntity
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

    /**
     * Get the options for the pdf creator
     * todo: ajust based on the format
     * @return Options
     */
    public function getOptions(): Options
    {
        return (new Options())
            ->withPrintHeader(true)
            ->withPrintFooter(true)
            ->withHeaderMargin(self::HEADER_HEIGTH)
            ->withFooterMargin(self::FOOTER_HEIGHT)
            ->withTopMargin(10 + self::HEADER_HEIGTH)
            ->withBottomMargin(10 + self::FOOTER_HEIGHT)
            ->withLeftMargin(10)
            ->withRightMargin(10);
    }
}
