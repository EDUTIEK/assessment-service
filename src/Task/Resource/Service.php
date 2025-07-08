<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Resource;

use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Task\Data\ResourceAvailability;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

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

    public function allByTypes(array $types) : array
    {
        $resources = [];
        foreach ($this->all() as $resource) {
            if (in_array($resource->getType(), $types, true)) {
                $resources[] = $resource;
            }
        }
        return $resources;
    }


    public function new(): Resource
    {
        return $this->repos->resource()->new()
            ->setTaskId($this->task_id);
    }

    public function one(int $id): ?Resource
    {
        $resource = $this->repos->resource()->one($id);
        if ($resource !== null) {
            $this->checkScope($resource);
            return $resource;
        }
        return null;
    }

    public function oneByType(ResourceType $type): ?Resource
    {
        $resource = $this->repos->resource()->oneByTaskIdAndType($this->task_id, $type);
        if ($resource !== null) {
            $this->checkScope($resource);
            return $resource;
        }
        return null;
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
            $this->storage->deleteFile($resource->getFileId() ?? '');
        }
    }

    public function isAvailable(OrgaSettings $orga, Resource $resource): bool
    {
        if ($resource->getAvailability() === ResourceAvailability::BEFORE) {
            return true;
        }

        if ($resource->getAvailability() === ResourceAvailability::DURING
            && time() < $orga->getWritingStart()?->getTimestamp()) {
            return true;
        }

        return $resource->getAvailability() === ResourceAvailability::AFTER
            && $orga->getSolutionAvailable()
            && time() < $orga->getSolutionAvailableDate()->getTimestamp();
    }

    private function checkScope(Resource $resource)
    {
        if ($resource->getTaskId() !== $this->task_id) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }
}
