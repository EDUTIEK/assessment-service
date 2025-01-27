<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Data\ConfigRepo;
use Edutiek\AssessmentService\System\Data\UserRepo;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;

interface Dependencies
{
    public function configRepo(): ConfigRepo;
    public function fileStorage(): Storage;
    public function fileDelivery(): Delivery;
    public function userRepo(): UserRepo;
}
