<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\System\Data\FileInfo;

/**
 * Functions to provide and process data for an app
 * Must be implemented by Assessment, Task and all Task Type components
 */
interface AppBridge
{
    /**
     * Get all data to open the app
     * @return array - will converted to JSON
     */
    public function getData(bool $for_update): array;

    /**
     * Get the file info of a file associated with an entity
     */
    public function getFileInfo(string $entity, int $entity_id): ?FileInfo;

    /**
     * Apply change requests
     * @param ChangeRequest[] $changes
     * @return ChangeResponse[]
     */
    public function applyChanges(string $type, array $changes): array;
}
