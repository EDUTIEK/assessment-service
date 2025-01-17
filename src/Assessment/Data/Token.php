<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Token implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getUserId(): int;
    public abstract function setUserId(int $user_id): self;
    public abstract function getToken(): string;
    public abstract function setToken(string $token): self;
    public abstract function getIp(): string;
    public abstract function setIp(string $ip): self;
    public abstract function getPurpose(): string;
    public abstract function setPurpose(string $purpose): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
    public abstract function getValidUntil(): ?DateTimeImmutable;
    public abstract function setValidUntil(?DateTimeImmutable $valid_until): self;
}
