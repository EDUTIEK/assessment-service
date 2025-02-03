<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Token implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getUserId(): int;
    abstract public function setUserId(int $user_id): self;
    abstract public function getToken(): string;
    abstract public function setToken(string $token): self;
    abstract public function getIp(): string;
    abstract public function setIp(string $ip): self;
    abstract public function getPurpose(): string;
    abstract public function setPurpose(string $purpose): self;
    abstract public function getValidUntil(): ?DateTimeImmutable;
    abstract public function setValidUntil(?DateTimeImmutable $valid_until): self;
}
