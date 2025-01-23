<?php

namespace Edutiek\AssessmentService\System\Data;

/**
 * Information about a file stored in the system
 */
abstract class FileInfo implements SystemEntity
{
    /**
     * ID under which the file is stored in the hosting system
     */
    abstract public function getId(): ?string;
    abstract public function setId(?string $id): FileInfo;

    /**
     * Name of the file which is used for downloads
     */
    abstract public function getFileName(): ?string;
    abstract public function setFileName(?string $name): FileInfo;

    /**
     * Mime type of the file
     */
    abstract public function getMimeType(): ?string;
    abstract public function setMimeType(?string $mime): FileInfo;

    /**
     * Size of the file in bytes
     */
    abstract public function getSize(): ?int;
    abstract public function setSize(?int $size): FileInfo;
}
