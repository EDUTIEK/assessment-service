<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class Resource implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getTitle(): string;
    abstract public function setTitle(string $title): self;
    abstract public function getDescription(): ?string;
    abstract public function setDescription(?string $description): self;
    abstract public function getUrl(): string;
    abstract public function setUrl(string $url): self;
    abstract public function getType(): string;
    abstract public function setType(string $type): self;
    abstract public function getAvailability(): string;
    abstract public function setAvailability(string $availability): self;
    abstract public function getFileId(): ?string;
    abstract public function setFileId(?string $file_id): self;
}
