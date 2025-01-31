<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class PdfSettings implements AssessmentEntity
{
    abstract public function getAddHeader(): int;
    abstract public function setAddHeader(int $add_header): self;
    abstract public function getAddFooter(): int;
    abstract public function setAddFooter(int $add_footer): self;
    abstract public function getTopMargin(): int;
    abstract public function setTopMargin(int $top_margin): self;
    abstract public function getBottomMargin(): int;
    abstract public function setBottomMargin(int $bottom_margin): self;
    abstract public function getLeftMargin(): int;
    abstract public function setLeftMargin(int $left_margin): self;
    abstract public function getRightMargin(): int;
    abstract public function setRightMargin(int $right_margin): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
}
