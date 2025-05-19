<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class WritingSettings implements EssayTaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getHeadlineScheme(): HeadlineScheme;
    abstract public function setHeadlineScheme(HeadlineScheme $headline_scheme): self;
    abstract public function getFormattingOptions(): FormattingOptions;
    abstract public function setFormattingOptions(FormattingOptions $formatting_options): self;
    abstract public function getNoticeBoards(): int;
    abstract public function setNoticeBoards(int $notice_boards): self;
    abstract public function getCopyAllowed(): bool;
    abstract public function setCopyAllowed(bool $copy_allowed): self;
    abstract public function getAddParagraphNumbers(): bool;
    abstract public function setAddParagraphNumbers(bool $add_paragraph_numbers): self;
    abstract public function getAddCorrectionMargin(): bool;
    abstract public function setAddCorrectionMargin(bool $add_correction_margin): self;
    abstract public function getLeftCorrectionMargin(): int;
    abstract public function setLeftCorrectionMargin(int $left_correction_margin): self;
    abstract public function getRightCorrectionMargin(): int;
    abstract public function setRightCorrectionMargin(int $right_correction_margin): self;
    abstract public function getAllowSpellcheck(): bool;
    abstract public function setAllowSpellcheck(bool $allow_spellcheck): self;
    abstract public function getWritingType(): WritingType;
    abstract public function setWritingType(WritingType $writing_type): self;
}
