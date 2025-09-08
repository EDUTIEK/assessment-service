<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class EssayImport implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function getFileId(): string;
    abstract public function getPassword(): ?string;
    abstract public function getExpectedHash(): ?string;
    abstract public function setId(int $id): self;
    abstract public function setFileId(string $file_id): self;
    abstract public function setPassword(?string $password): self;
    abstract public function setExpectedHash(?string $hash): self;
}
