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

abstract class RatingCriteria implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTitle(): string;
    public abstract function setTitle(string $title): void;
    public abstract function getDescription(): ?string;
    public abstract function setDescription(?string $description): void;
    public abstract function getPoints(): int;
    public abstract function setPoints(int $points): void;
    public abstract function getCorrectorId(): ?int;
    public abstract function setCorrectorId(?int $corrector_id): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
}
