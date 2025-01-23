<?php

namespace Edutiek\AssessmentService\System\File;

use Edutiek\AssessmentService\System\Data\FileInfo;

interface Storage
{
    /**
     * Get basic information about a saved file
     * The function will return null if the file is not found
     */
    public function getFileInfo(string $id): ?FileInfo;

    /**
     * Get the resource handle of an opened file stream for reading
     * @return resource|null
     */
    public function getFileStream(string $id): mixed;

    /**
     * Save a file from an open stream for reading
     * If the provided FileInfo has an id, then this file is replaced
     * Other data is updated from the FileInfo
     * The returned FileInfo object provides the id of the saved file
     * The function will return null if the file could not be saved
     * @param resource $stream
     */
    public function saveFile(mixed $stream, ?FileInfo $info): ?FileInfo;

    /**
     * Delete a file with the given id
     */
    public function deleteFile(string $id): void;
}
