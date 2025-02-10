<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfCreator;

interface PdfSettings
{
    public function getAddHeader() : bool;
    public function getAddFooter() : bool;
    public function getTopMargin() : int;
    public function getBottomMargin() : int;
    public function getLeftMargin() : int;
    public function getRightMargin() : int;
    public function getHeaderMargin() : int;
    public function getFooterMargin()  : int;
    public function getContentTopMargin() : int;
    public function getContentBottomMargin() : int;
}