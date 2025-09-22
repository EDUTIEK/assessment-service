<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfCreator;

use Edutiek\AssessmentService\System\PdfCreator\PdfSettings;

class PlainSettings implements PdfSettings
{
    public function getAddHeader(): bool
    {
        return false;
    }

    public function getAddFooter(): bool
    {
        return false;
    }

    public function getTopMargin(): int
    {
        return 0;
    }

    public function getBottomMargin(): int
    {
        return 0;
    }

    public function getLeftMargin(): int
    {
        return 0;
    }

    public function getRightMargin(): int
    {
        return 0;
    }

    public function getHeaderMargin(): int
    {
        return 0;
    }

    public function getFooterMargin(): int
    {
        return 0;
    }

    public function getContentTopMargin(): int
    {
        return 0;
    }

    public function getContentBottomMargin(): int
    {
        return 0;
    }
}
