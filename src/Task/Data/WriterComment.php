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

namespace Edutiek\AssessmentService\Task\Data;

abstract class WriterComment implements TaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
    public abstract function getComment(): ?string;
    public abstract function setComment(?string $comment): void;
    public abstract function getStartPosition(): int;
    public abstract function setStartPosition(int $start_position): void;
    public abstract function getEndPosition(): int;
    public abstract function setEndPosition(int $end_position): void;
}
