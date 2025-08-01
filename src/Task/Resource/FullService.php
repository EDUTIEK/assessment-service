<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Resource;

use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

interface FullService
{
    /** @return Resource[] */
    public function all(): array;
    /**
     * @param ResourceType[]
     * @return Resource[]
     * */
    public function allByTypes(array $types): array;
    public function new(): Resource;
    public function one(int $id): ?Resource;
    public function oneByType(ResourceType $type): ?Resource;
    public function validate(Resource $resource): bool;
    public function save(Resource $resource): void;
    public function delete(Resource $resource): void;
    public function isAvailable(OrgaSettings $orga, Resource $resource): bool;
}
