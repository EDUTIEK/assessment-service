<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\Config\Service as ConfigService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\System\User\Service as UserService;

class ForServices
{
    protected static array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    public function config(): ConfigReadService
    {
        return self::$instances[ConfigService::class] ??= new ConfigService(
            $this->dependencies->configRepo()
        );
    }

    public function fileStorage(): Storage
    {
        return $this->dependencies->fileStorage();
    }

    public function fileDelivery(): Delivery
    {
        return $this->dependencies->fileDelivery();
    }

    public function user(): UserReadService
    {
        return self::$instances[UserService::class] ??= new UserService(
            $this->dependencies->userRepo()
        );
    }
}
