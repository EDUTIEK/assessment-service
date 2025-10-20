<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

/**
 * Functions to provide and process data for the assessment-writer WebApp
 * Must be implemented by System, Task and all Task Type components
 */
interface WriterBridge
{
    /**
     * Get all data to open the the writer app
     * @return array - will converted to JSON
     */
    public function getData(): array;

    /**
     * Get the data for periodic updates (e.g. every second)
     * @return array - will converted to JSON
     */
    public function getUpdate(): array;


    /**
     * Get the file id of a file associated with an entity
     */
    public function getFileId(string $entity, int $entity_id): ?string;
}
