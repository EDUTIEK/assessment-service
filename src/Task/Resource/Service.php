<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Resource;

use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceType;

readonly class Service implements FullService
{
    public function __construct(
        private int $task_id,
        private Repositories $repos,
        private Storage $storage,
    ) {
    }

    public function all(): array
    {
        return $this->repos->resource()->allByTaskId($this->task_id);
    }

    public function one(int $id): ?Resource
    {
        $resource = $this->repos->resource()->one($id);
        if ($resource !== null) {
            $this->checkScope($resource);
            return $resource;
        }
        return $resource;
    }

    public function oneByType(ResourceType $type): Resource
    {
        $resource = $this->repos->resource()->oneByTaskIdAndType($this->task_id, $type);
        if ($resource !== null) {
            $this->checkScope($resource);
            return $resource;
        }
        return $resource;
    }

    public function validate(Resource $resource): bool
    {
        $this->checkScope($resource);
        return true;
    }

    public function save(Resource $resource): void
    {
        $this->checkScope($resource);
        $this->repos->resource()->save($resource);
    }

    public function delete(Resource $resource): void
    {
        $this->checkScope($resource);
        $this->repos->resource()->delete($resource->getId());
        if ($resource->getFileId() !== null) {
            $this->storage->deleteFile($resource->getFileId());
        }
    }

    private function checkScope(Resource $resource)
    {
        if ($resource->getTaskId() !== $this->task_id) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }
}
