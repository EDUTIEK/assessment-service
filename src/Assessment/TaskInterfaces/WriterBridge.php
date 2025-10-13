<?php

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

/**
 * Functions to provide and process task data for the assessment-writer WebApp
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
}
