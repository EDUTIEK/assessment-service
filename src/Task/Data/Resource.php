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

abstract class Resource implements TaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
    public abstract function getTitle(): string;
    public abstract function setTitle(string $title): void;
    public abstract function getDescription(): ?string;
    public abstract function setDescription(?string $description): void;
    public abstract function getUrl(): string;
    public abstract function setUrl(string $url): void;
    public abstract function getType(): string;
    public abstract function setType(string $type): void;
    public abstract function getAvailability(): string;
    public abstract function setAvailability(string $availability): void;
    public abstract function getFileId(): ?string;
    public abstract function setFileId(?string $file_id): void;
}
