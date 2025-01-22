<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\ReadService;
use Edutiek\AssessmentService\System\Config\Service;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;

class ForServices
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    public function config(): ReadService
    {
        return self::$instances[ReadService::class] ??= new Service(
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
}
