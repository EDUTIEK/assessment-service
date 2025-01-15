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

use DateTimeImmutable;

abstract class WriterHistory implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): void;
    public abstract function getTimestamp(): ?DateTimeImmutable;
    public abstract function setTimestamp(?DateTimeImmutable $timestamp): void;
    public abstract function getContent(): ?string;
    public abstract function setContent(?string $content): void;
    public abstract function getIsDelta(): int;
    public abstract function setIsDelta(int $is_delta): void;
    public abstract function getHashBefore(): ?string;
    public abstract function setHashBefore(?string $hash_before): void;
    public abstract function getHashAfter(): ?string;
    public abstract function setHashAfter(?string $hash_after): void;
}
