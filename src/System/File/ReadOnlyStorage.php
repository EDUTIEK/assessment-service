<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\File;

use Edutiek\AssessmentService\System\Data\FileInfo;

interface ReadOnlyStorage
{
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
}
