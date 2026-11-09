<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use DateTimeImmutable;

abstract class Token implements SystemEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getUserId(): int;
    abstract public function setUserId(int $user_id): self;
    abstract public function getToken(): string;
    abstract public function setToken(string $token): self;
    abstract public function getValidUntil(): ?DateTimeImmutable;
    abstract public function setValidUntil(?DateTimeImmutable $valid_until): self;

    public function isValid(): bool
    {
        return $this->getValidUntil() === null || $this->getValidUntil()->getTimestamp() > time();
    }

    public function setNewValidity(): self
    {
        $this->setValidUntil(null);
        return $this;
    }
}
