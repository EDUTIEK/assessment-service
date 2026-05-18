<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSnippets;

use Edutiek\AssessmentService\Task\Data\CorrectorSnippetPurpose;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Psr\Http\Message\UploadedFileInterface;

interface FullService
{
    public function json(int $corrector_id): array;
    public function export(int $corrector_id, CorrectorSnippetPurpose $purpose): FileInfo;
    public function import(UploadedFileInterface $file, int $corrector_id, CorrectorSnippetPurpose $purpose): ?array;
}
