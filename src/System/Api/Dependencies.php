<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use DateTimeInterface;
use Edutiek\AssessmentService\System\BackgroundTask\ClientManager as BackgroundTaskManager;
use Edutiek\AssessmentService\System\Data\ConfigRepo;
use Edutiek\AssessmentService\System\Data\SetupRepo;
use Edutiek\AssessmentService\System\Data\UserDataRepo;
use Edutiek\AssessmentService\System\Data\UserDisplayRepo;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Log\FullService as Logger;
use Edutiek\AssessmentService\System\Session\Storage as SessionStorage;

interface Dependencies
{
    public function configRepo(): ConfigRepo;
    public function setupRepo(): SetupRepo;
    public function fileStorage(): Storage;
    public function fileDelivery(): Delivery;
    public function tempStorage(): Storage;
    public function tempDelivery(): Delivery;
    public function userDataRepo(): UserDataRepo;
    public function userDisplayRepo(): UserDisplayRepo;
    public function formatDate(DateTimeInterface $date): string;
    public function backgroundTaskManager(): BackgroundTaskManager;
    public function sessionStorage(): SessionStorage;
    public function log(): Logger;
}
