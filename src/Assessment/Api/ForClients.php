<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;


use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly Dependencies $dependencies
    )
    {
    }

    public function permissions(int $user_id): PermissionsReadService
    {
        return $this->instances[PermissionsService::class][$user_id] ??= new PermissionsService(
            $this->ass_id,
            $this->context_id,
            $user_id,
            $this->dependencies->objectRepo()
        );
    }
}
