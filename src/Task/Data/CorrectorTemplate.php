<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use Edutiek\AssessmentService\System\Api\HasHtml;

abstract class CorrectorTemplate implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): void;
    abstract public function getCorrectorId(): int;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getShared(): bool;
    abstract public function setShared(bool $shared): self;
    #[HasHtml]
    abstract public function getContent(): ?string;
    abstract public function setContent(?string $content): self;
}
