<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

use Psr\Http\Message\UploadedFileInterface;

interface AppCorrectorBridge extends AppBridge
{
    /**
     * Set if the current user is able to maintain corrections
     */
    public function setAdmin(bool $is_admin): static;

    /**
     * Get the data of a correction item
     */
    public function getItem(int $task_id, int $writer_id): ?array;

    /**
     * Process an uploaded file and return json data as array
     */
    public function processUploadedFile(UploadedFileInterface $file, string $entity, ?int $task_id, ?int $writer_id): ?array;
}
