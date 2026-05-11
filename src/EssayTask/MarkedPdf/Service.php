<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\MarkedPdf;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;
use Edutiek\AssessmentService\EssayTask\Data\MarkedPdf;
use Edutiek\AssessmentService\EssayTask\Data\MarkedPdfRepo;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\Checks\FullService as ChecksService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;

class Service implements UsageService, EventService
{
    private MarkedPdfRepo $repo;

    public function __construct(
        private Repositories $repos,
        private ChecksService $checks,
        private GradingProvider $gradings,
        private Storage $storage,
    ) {
        $this->repo = $this->repos->markedPdf();
    }


    public function ownByIds(int $task_id, int $writer_id, int $corrector_id): ?string
    {
        $this->checkScope($task_id, $writer_id, $corrector_id);
        return $this->repo->oneByIds($task_id, $writer_id, $corrector_id)?->getOwnPdf();
    }

    public function sumByIds(int $task_id, int $writer_id): ?string
    {
        $this->checkScope($task_id, $writer_id);

        $gradings = $this->gradings->gradingsForTaskAndWriter($task_id, $writer_id);
        $grading = null;
        if ($gradings[GradingPosition::STITCH->value]?->isAuthorized()) {
            $grading = $gradings[GradingPosition::STITCH->value];
        } elseif ($gradings[GradingPosition::SECOND->value]?->isAuthorized()) {
            $grading = $gradings[GradingPosition::SECOND->value];
        } elseif ($gradings[GradingPosition::FIRST->value]?->isAuthorized()) {
            $grading = $gradings[GradingPosition::FIRST->value];
        }

        if ($grading) {
            return $this->repo->oneByIds($grading->getTaskId(), $grading->getWriterId(), $grading->getCorrectorId())->getSumPdf();
        }
        return null;
    }

    public function saveOwn(string $file_id, int $task_id, int $writer_id, int $corrector_id): void
    {
        $this->checkScope($task_id, $writer_id, $corrector_id);
        $entity = $this->repo->oneByIds($task_id, $writer_id, $corrector_id) ?? (
            $this->repo->new()
            ->setTaskId($task_id)
            ->setWriterId($writer_id)
            ->setCorrectorId($corrector_id)
        );
        if ($entity->getOwnPdf()) {
            $this->storage->deleteFile($entity->getOwnPdf());
        }
        $this->repo->save($entity->setOwnPdf($file_id));
    }

    public function saveSum(string $file_id, int $task_id, int $writer_id, int $corrector_id): void
    {
        $this->checkScope($task_id, $writer_id, $corrector_id);
        $entity = $this->repo->oneByIds($task_id, $writer_id, $corrector_id) ?? (
            $this->repo->new()
                   ->setTaskId($task_id)
                   ->setWriterId($writer_id)
                   ->setCorrectorId($corrector_id)
        );
        if ($entity->getSumPdf()) {
            $this->storage->deleteFile($entity->getSumPdf());
        }
        $this->repo->save($entity->setSumPdf($file_id));
    }

    public function delete(int $task_id, int $writer_id, int $corrector_id): void
    {
        $this->checkScope($task_id, $writer_id);
        if ($entity = $this->repo->oneByIds($task_id, $writer_id, $corrector_id)) {
            $this->deleteFilesAndEntity($entity);
        }
    }

    public function deleteByTaskId(int $task_id): void
    {
        foreach ($this->repo->allByTaskId($task_id) as $entity) {
            $this->deleteFilesAndEntity($entity);
        }
    }

    public function deleteByWriterId(int $writer_id): void
    {
        foreach ($this->repo->allByWriterId($writer_id) as $entity) {
            $this->deleteFilesAndEntity($entity);
        }
    }

    public function deleteByCorrectorId(int $corrector_id): void
    {
        foreach ($this->repo->allByCorrectorId($corrector_id) as $entity) {
            $this->deleteFilesAndEntity($entity);
        }
    }

    /**
     * Delete the entity and referenced pdf files
     */
    private function deleteFilesAndEntity(MarkedPdf $entity): void
    {
        $this->storage->deleteFile($entity->getOwnPdf());
        $this->storage->deleteFile($entity->getSumPdf());
        $this->repo->delete($entity->getId());
    }

    /**
     * Check if an operation is allowed in the current scope
     */
    private function checkScope(int $task_id, int $writer_id, ?int $corrector_id = null): void
    {
        if (!$this->checks->hasTask($task_id)) {
            throw new ApiException("wrong task", ApiException::ID_SCOPE);
        }

        if (!$this->checks->hasWriter($writer_id)) {
            throw new ApiException("wrong writer", ApiException::ID_SCOPE);
        }

        if ($corrector_id !== null) {
            if (!$this->checks->hasCorrector($corrector_id)) {
                throw new ApiException("wrong corrector", ApiException::ID_SCOPE);
            }
            if (!$this->checks->isAssigned($writer_id, $corrector_id, $task_id)) {
                throw new ApiException("corrector not assigned to writer", ApiException::ID_SCOPE);
            }
        }
    }
}
