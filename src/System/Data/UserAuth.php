<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use DateTimeImmutable;

abstract class UserAuth implements SystemEntity
{
    private const int DEFAULT_RESET_CODE_VALIDITY_SEC = 500;

    public static function createCode(): string
    {
        return bin2hex(random_bytes(3));
    }

    private static function newResetCodeValidity(): int
    {
        return time() + self::DEFAULT_RESET_CODE_VALIDITY_SEC;
    }


    abstract public function getUserId(): int;
    abstract public function setUserId(int $user_id): self;
    abstract public function getResetCode(): ?string;
    abstract public function setResetCode(?string $code): self;
    abstract public function getResetValidUntil(): ?DateTimeImmutable;
    abstract public function setResetValidUntil(?DateTimeImmutable $reset_valid_until): self;
    abstract public function getFactorInitCode(): ?string;
    abstract public function setFactorInitCode(?string $code): self;

    public function isResetCodeValid($code): bool
    {
        return $code == $this->getResetCode() && $this->getResetValidUntil()->getTimestamp() > time();
    }

    /**
     * Avoid DOS attacs and allow sending anew code only after 5 seconds
     */
    public function isNewResetCodePossible(): bool
    {
        return $this->getResetValidUntil()->getTimestamp() < self::newResetCodeValidity() - 5;
    }


    public function setNewResetCode($code): self
    {
        $this->setResetCode($code);
        $this->setResetValidUntil(new DateTimeImmutable('@' . self::newResetCodeValidity()));
        return $this;
    }
}
