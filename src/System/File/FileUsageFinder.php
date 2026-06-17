<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\File;

/**
 * This interface must be provided by all components that use the file storage
 * Stored files of a component that are not found by its finder will be deleted in a cleanup process
 */
interface FileUsageFinder
{
    /**
     * Get a list of used file ids with a certain usage type
     * @return string[]
     */
    public function usedIds(): array;
}
