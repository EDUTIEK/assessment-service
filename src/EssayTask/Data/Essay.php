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

abstract class Essay implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getUuid(): string;
    public abstract function setUuid(string $uuid): void;
    public abstract function getWriterId(): int;
    public abstract function setWriterId(int $writer_id): void;
    public abstract function getWrittenText(): ?string;
    public abstract function setWrittenText(?string $written_text): void;
    public abstract function getRawTextHash(): string;
    public abstract function setRawTextHash(string $raw_text_hash): void;
    public abstract function getPdfVersion(): ?string;
    public abstract function setPdfVersion(?string $pdf_version): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
    public abstract function getLastChange(): ?DateTimeImmutable;
    public abstract function setLastChange(?DateTimeImmutable $last_change): void;
    public abstract function getServiceVersion(): int;
    public abstract function setServiceVersion(int $service_version): void;
    public abstract function getFirstChange(): ?DateTimeImmutable;
    public abstract function setFirstChange(?DateTimeImmutable $first_change): void;
}
