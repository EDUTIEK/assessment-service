<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Data\ConfigRepo;
use Edutiek\AssessmentService\System\Data\UserDataRepo;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\Data\SetupRepo;
use Edutiek\AssessmentService\System\Data\UserDisplayRepo;
use DateTimeInterface;
use Edutiek\AssessmentService\System\BackgroundTask\ClientManager as BackgroundTaskManager;

interface Dependencies
{
    public function configRepo(): ConfigRepo;
    public function setupRepo(): SetupRepo;
    public function fileStorage(): Storage;
    public function fileDelivery(): Delivery;
    public function userDataRepo(): UserDataRepo;
    public function userDisplayRepo(): UserDisplayRepo;
    public function formatDate(DateTimeInterface $date): string;
    public function backgroundTaskManager(): BackgroundTaskManager;
}
