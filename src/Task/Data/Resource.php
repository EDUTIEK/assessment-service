<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class Resource implements TaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): self;
    public abstract function getTitle(): string;
    public abstract function setTitle(string $title): self;
    public abstract function getDescription(): ?string;
    public abstract function setDescription(?string $description): self;
    public abstract function getUrl(): string;
    public abstract function setUrl(string $url): self;
    public abstract function getType(): string;
    public abstract function setType(string $type): self;
    public abstract function getAvailability(): string;
    public abstract function setAvailability(string $availability): self;
    public abstract function getFileId(): ?string;
    public abstract function setFileId(?string $file_id): self;
}
