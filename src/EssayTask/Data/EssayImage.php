<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class EssayImage implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): void;
    public abstract function getPageNo(): int;
    public abstract function setPageNo(int $page_no): void;
    public abstract function getWidth(): int;
    public abstract function setWidth(int $width): void;
    public abstract function getHeight(): int;
    public abstract function setHeight(int $height): void;
    public abstract function getMime(): string;
    public abstract function setMime(string $mime): void;
    public abstract function getThumbWidth(): ?int;
    public abstract function setThumbWidth(?int $thumb_width): void;
    public abstract function getThumbHeight(): ?int;
    public abstract function setThumbHeight(?int $thumb_height): void;
    public abstract function getThumbMime(): ?string;
    public abstract function setThumbMime(?string $thumb_mime): void;
    public abstract function getFileId(): string;
    public abstract function setFileId(string $file_id): void;
    public abstract function getThumbId(): ?string;
    public abstract function setThumbId(?string $thumb_id): void;
}
