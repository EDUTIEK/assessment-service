<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

/**
 * Functions to provide and process data for the assessment-writer WebApp
 * Must be implemented by Assessment, Task and all Task Type components
 */
interface WriterBridge
{
    /**
     * Get all data to open the the writer app
     * @return array - will converted to JSON
     */
    public function getData(bool $for_update): array;

    /**
     * Get the file id of a file associated with an entity
     */
    public function getFileId(string $entity, int $entity_id): ?string;

    /**
     * Apply a data change request
     */
    public function applyChange(ChangeRequest $change): ChangeResponse;
}
