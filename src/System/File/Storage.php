<?php

namespace Edutiek\AssessmentService\System\File;

use Edutiek\AssessmentService\System\Data\FileInfo;

Interface Storage
{
    /**
     * Get basic information about a saved file
     */
    public function getFileInfo(string $id): FileInfo;

    /**
     * Get the resource handle of an opened file stream for reading
     * @return resource
     */
    public function getFileStream(string $id): mixed;

    /**
     * Save a file from an open stream for reading
     * If the provided FileInfo has an id, then this file os replaced
     * Other data is updated from the FileInfo
     * The returned FileInfo object provides the id of the saved file
     * @param resource $stream
     */
    public function saveFile(mixed $stream, ?FileInfo $info): FileInfo;

    /**
     * Delete a file with the given id
     */
    public function deleteFile(string $id): void;
}