<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\MarkedPdf;

use Edutiek\AssessmentService\EssayTask\Data\MarkedPdf;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\Checks\FullService as ChecksService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentService;

class Service implements UsageService, EventService
{
    public function __construct(
        private Repositories $repos,
        private Storage $storage,
        ChecksService $checks,
        CorrectorAssignmentService $assignments,
    ) {

    }

    public function deleteByTaskId(int $task_id): void
    {
        // TODO: Implement deleteByTaskId() method.
    }

    public function deleteByWriterId(int $writer_id): void
    {
        // TODO: Implement deleteByWriterId() method.
    }

    public function deleteByCorrectorId(int $corrector_id): void
    {
        // TODO: Implement deleteByCorrectorId() method.
    }

    public function ByIds(int $task_id, int $writer_id, int $corrector_id): ?string
    {
        // TODO: Implement ownByIds() method.
        return null;
    }

    public function sumByIds(int $task_id, int $writer_id): ?string
    {
        // TODO: Implement allByIds() method.
    }

    public function save(string $own_id, string $all_id, int $task_id, int $writer_id, int $corrector_id): void
    {
        // TODO: Implement save() method.
    }

    public function delete(int $task_id, int $writer_id, int $corrector_id): void
    {
        // TODO: Implement delete() method.
    }
}
