<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\FullService as ConfigFullService;
use Edutiek\AssessmentService\System\Config\Service as ConfigService;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\Entity\Service as EntityService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\System\Format\Service as FormatService;
use Edutiek\AssessmentService\System\Transform\FullService as TransformFullService;
use Edutiek\AssessmentService\System\Transform\Service as TransformService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\System\User\Service as UserService;
use Closure;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function config(): ConfigFullService
    {
        return $this->instances[ConfigService::class] ??= new ConfigService(
            $this->dependencies->configRepo(),
            $this->dependencies->setupRepo()
        );
    }

    public function entity(): EntityFullService
    {
        return $this->instances[EntityService::class] ??= new EntityService();
    }

    public function fileStorage(): Storage
    {
        return $this->dependencies->fileStorage();
    }

    public function fileDelivery(): Delivery
    {
        return $this->dependencies->fileDelivery();
    }

    public function format(int $user_id, ?string $timezone = null): FormatFullService
    {
        $timezone ??= $this->config()->getSetup()->getDefaultTimezone();
        return new FormatService($this->dependencies->formatDate(...), $timezone, $this->internal->language($user_id));
    }

    public function transform(): TransformFullService
    {
        return $this->instances[TransformService::class] ??= new TransformService();
    }

    public function user(): UserReadService
    {
        return $this->instances[UserService::class] ??= new UserService(
            $this->dependencies->userDataRepo(),
            $this->dependencies->userDisplayRepo()
        );
    }

}
