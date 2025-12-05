<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

use Psr\Http\Message\UploadedFileInterface;

interface AppCorrectorBridge extends AppBridge
{
    public function getItem(int $task_id, int $writer_id): ?array;

    public function processUploadedFile(UploadedFileInterface $file, int $task_id, int $writer_id): ?string;
}
