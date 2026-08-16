<?php

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class WriterClient implements AssessmentEntity
{
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): static;

    abstract public function getTokenId(): int;
    abstract public function setTokenId(int $token_id): static;

    abstract public function getSessionId(): ?string;
    abstract public function setSessionId(?string $session_id): static;

    abstract public function getFirstAccess(): DateTimeImmutable;
    abstract public function setFirstAccess(DateTimeImmutable $first_access): static;

    abstract public function getLastAccess(): DateTimeImmutable;
    abstract public function setLastAccess(DateTimeImmutable $last_access): static;

    abstract public function getIp(): ?string;
    abstract public function setIp(?string $ip): static;

    abstract public function getUserAgent(): ?string;
    abstract public function setUserAgent(?string $user_agent): static;

    abstract public function getPlatform(): ?string;
    abstract public function setPlatform(?string $platform): static;

    abstract public function getBattery(): ?float;
    abstract public function setBattery(?float $battery): static;

    abstract public function getHidden(): ?bool;
    abstract public function setHidden(?bool $hidden): static;
}
