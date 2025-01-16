<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class PdfSettings implements AssessmentEntity
{
    public abstract function getAddHeader(): int;
    public abstract function setAddHeader(int $add_header): void;
    public abstract function getAddFooter(): int;
    public abstract function setAddFooter(int $add_footer): void;
    public abstract function getTopMargin(): int;
    public abstract function setTopMargin(int $top_margin): void;
    public abstract function getBottomMargin(): int;
    public abstract function setBottomMargin(int $bottom_margin): void;
    public abstract function getLeftMargin(): int;
    public abstract function setLeftMargin(int $left_margin): void;
    public abstract function getRightMargin(): int;
    public abstract function setRightMargin(int $right_margin): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
}
