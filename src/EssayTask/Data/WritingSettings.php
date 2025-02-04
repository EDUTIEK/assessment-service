<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class WritingSettings implements EssayTaskEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getHeadlineScheme(): string;
    abstract public function setHeadlineScheme(string $headline_scheme): self;
    abstract public function getFormattingOptions(): string;
    abstract public function setFormattingOptions(string $formatting_options): self;
    abstract public function getNoticeBoards(): int;
    abstract public function setNoticeBoards(int $notice_boards): self;
    abstract public function getCopyAllowed(): int;
    abstract public function setCopyAllowed(int $copy_allowed): self;
    abstract public function getAddParagraphNumbers(): int;
    abstract public function setAddParagraphNumbers(int $add_paragraph_numbers): self;
    abstract public function getAddCorrectionMargin(): int;
    abstract public function setAddCorrectionMargin(int $add_correction_margin): self;
    abstract public function getLeftCorrectionMargin(): int;
    abstract public function setLeftCorrectionMargin(int $left_correction_margin): self;
    abstract public function getRightCorrectionMargin(): int;
    abstract public function setRightCorrectionMargin(int $right_correction_margin): self;
    abstract public function getAllowSpellcheck(): int;
    abstract public function setAllowSpellcheck(int $allow_spellcheck): self;
    abstract public function getWritingType(): string;
    abstract public function setWritingType(string $writing_type): self;
}
