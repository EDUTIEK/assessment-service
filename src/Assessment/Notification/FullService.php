<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Notification;

use Edutiek\AssessmentService\Assessment\Data\NotificationSettings;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\Assessment\Data\NotificationUser;
use Edutiek\AssessmentService\Assessment\Data\NotificationQueue;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\Corrector;

interface FullService extends DeliverService
{
    public function newSettings(): NotificationSettings;

    public function settingsById(int $id): ?NotificationSettings;

    public function getSettings(NotificationType $type): NotificationSettings;

    /** @return NotificationSettings[] */
    public function allSettings(): array;

    /** @return NotificationUser[] */
    public function usersByType(NotificationType $type): array;

    /** @return NotificationQueue[] */
    public function queueByType(NotificationType $type): array;

    public function saveSettings(NotificationSettings $settings): void;

    /** @param int[] $user_ids */
    public function saveUsers(NotificationType $type, array $user_ids): void;

    /** @param int[] $user_ids */
    public function saveQueue(NotificationType $type, array $user_ids): void;

    public function getPlaceholderInfo(NotificationType $type): string;
}
