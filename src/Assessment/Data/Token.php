<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Token implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getUserId(): int;
    public abstract function setUserId(int $user_id): void;
    public abstract function getToken(): string;
    public abstract function setToken(string $token): void;
    public abstract function getIp(): string;
    public abstract function setIp(string $ip): void;
    public abstract function getPurpose(): string;
    public abstract function setPurpose(string $purpose): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
    public abstract function getValidUntil(): ?DateTimeImmutable;
    public abstract function setValidUntil(?DateTimeImmutable $valid_until): void;
}
