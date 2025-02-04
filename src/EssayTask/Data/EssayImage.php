<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class EssayImage implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getEssayId(): int;
    abstract public function setEssayId(int $essay_id): self;
    abstract public function getPageNo(): int;
    abstract public function setPageNo(int $page_no): self;
    abstract public function getWidth(): int;
    abstract public function setWidth(int $width): self;
    abstract public function getHeight(): int;
    abstract public function setHeight(int $height): self;
    abstract public function getMime(): string;
    abstract public function setMime(string $mime): self;
    abstract public function getThumbWidth(): ?int;
    abstract public function setThumbWidth(?int $thumb_width): self;
    abstract public function getThumbHeight(): ?int;
    abstract public function setThumbHeight(?int $thumb_height): self;
    abstract public function getThumbMime(): ?string;
    abstract public function setThumbMime(?string $thumb_mime): self;
    abstract public function getFileId(): string;
    abstract public function setFileId(string $file_id): self;
    abstract public function getThumbId(): ?string;
    abstract public function setThumbId(?string $thumb_id): self;
}
