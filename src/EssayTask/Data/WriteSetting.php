<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class WriteSetting implements EssayTaskEntity
{
    public abstract function getHeadlineScheme(): string;
    public abstract function setHeadlineScheme(string $headline_scheme): void;
    public abstract function getFormattingOptions(): string;
    public abstract function setFormattingOptions(string $formatting_options): void;
    public abstract function getNoticeBoards(): int;
    public abstract function setNoticeBoards(int $notice_boards): void;
    public abstract function getCopyAllowed(): int;
    public abstract function setCopyAllowed(int $copy_allowed): void;
    public abstract function getAddParagraphNumbers(): int;
    public abstract function setAddParagraphNumbers(int $add_paragraph_numbers): void;
    public abstract function getAddCorrectionMargin(): int;
    public abstract function setAddCorrectionMargin(int $add_correction_margin): void;
    public abstract function getLeftCorrectionMargin(): int;
    public abstract function setLeftCorrectionMargin(int $left_correction_margin): void;
    public abstract function getRightCorrectionMargin(): int;
    public abstract function setRightCorrectionMargin(int $right_correction_margin): void;
    public abstract function getAllowSpellcheck(): int;
    public abstract function setAllowSpellcheck(int $allow_spellcheck): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
    public abstract function getWritingType(): string;
    public abstract function setWritingType(string $writing_type): void;
}
