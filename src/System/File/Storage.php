<?php

namespace Edutiek\AssessmentService\System\File;

use Edutiek\AssessmentService\System\Data\FileInfo;
use Psr\Http\Message\StreamInterface as Stream;

interface Storage extends ReadOnlyStorage
{
    /**
     * Save a file from an open stream for reading
     * If the provided FileInfo has an id, then this file is replaced
     * Other data is updated from the FileInfo
     * The returned FileInfo object provides the id of the saved file
     * The function will return null if the file could not be saved
     * @param Stream|string|resource|null $input
     */
    public function saveFile(mixed $input, ?FileInfo $info): ?FileInfo;

    /**
     * Delete a file with the given id
     */
    public function deleteFile(?string $id): void;
}
