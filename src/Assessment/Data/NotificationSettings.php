<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class NotificationSettings implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getType(): NotificationType;
    abstract public function setType(NotificationType $type): self;
    abstract public function isActive(): bool;
    abstract public function setActive(bool $active): self;
    abstract public function getSubject(): string;
    abstract public function setSubject(string $subject): self;
    abstract public function getBody(): ?string;
    abstract public function setBody(?string $body): self;
}
