<?php

namespace Edutiek\AssessmentService\System\File;

use Edutiek\AssessmentService\System\Data\FileInfo;
use Psr\Http\Message\StreamInterface as Stream;

interface Storage
{
    /**
     * Get a new file info
     */
    public function newInfo(): FileInfo;

    /**
     * Convert a filename to ASCII
     */
    public function asciiFilename(string $filename): string;

    /**
     * Check if a file with an id exists
     */
    public function hasFile(?string $id): bool;

    /**
     * Save a file from an open stream for reading
     * If the provided FileInfo has an id, then this file is replaced
     * Other data is updated from the FileInfo
     * The returned FileInfo object provides the id of the saved file
     * The function will return null if the file could not be saved
     * @param Stream|string|resource|null $input
     */
    public function saveFile(mixed $input, ?FileInfo $info = null): ?FileInfo;

    /**
     * Delete a file with the given id
     */
    public function deleteFile(?string $id): void;

    /**
     * Get basic information about a saved file
     * The function will return null if the file is not found
     */
    public function getFileInfo(?string $id): ?FileInfo;

    /**
     * Get the resource handle of an opened file stream for reading
     * @return resource|null
     */
    public function getFileStream(?string $id): mixed;


    /**
     * Get an absolute path from which the contents of a file can be read
     * Depending on the implementation of the storage service,
     * this may be the path of a temporary local copy of the file
     *
     * @param string|null $id
     * @return string|null  absolute file path or null if not found
     */
    public function getReadablePath(?string $id): ?string;

    /**
     * Get the root directory all getReadablePath() calls
     * Dompdf needs this to set the chroot directory
     */
    public function getReadableRoot(): string;
}
