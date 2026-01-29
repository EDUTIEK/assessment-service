<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

use Psr\Http\Message\UploadedFileInterface;

interface AppCorrectorBridge extends AppBridge
{
    /**
     * Set if the current user is able to maintain corrections
     */
    public function setAdmin(bool $is_admin): static;

    public function getItem(int $task_id, int $writer_id): ?array;

    public function processUploadedFile(UploadedFileInterface $file, int $task_id, int $writer_id): ?string;
}
