<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class PdfSettings implements AssessmentEntity
{
    public abstract function getAddHeader(): int;
    public abstract function setAddHeader(int $add_header): self;
    public abstract function getAddFooter(): int;
    public abstract function setAddFooter(int $add_footer): self;
    public abstract function getTopMargin(): int;
    public abstract function setTopMargin(int $top_margin): self;
    public abstract function getBottomMargin(): int;
    public abstract function setBottomMargin(int $bottom_margin): self;
    public abstract function getLeftMargin(): int;
    public abstract function setLeftMargin(int $left_margin): self;
    public abstract function getRightMargin(): int;
    public abstract function setRightMargin(int $right_margin): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
}
