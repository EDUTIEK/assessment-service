<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class NotificationQueue implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getUserId(): int;
    abstract public function setUserId(int $user_id): self;
    abstract public function getType(): NotificationType;
    abstract public function setType(NotificationType $type): self;
    abstract public function getAdded(): ?DateTimeImmutable;
    abstract public function setAdded(?DateTimeImmutable $added): self;
}
