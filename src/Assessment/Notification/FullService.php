<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Notification;

use Edutiek\AssessmentService\Assessment\Data\NotificationSettings;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\Assessment\Data\NotificationUser;
use Edutiek\AssessmentService\Assessment\Data\NotificationQueue;

interface FullService
{
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

    public function sendFor(NotificationType $type, int $writer_id): void;

    /** @return array placeholder => lang var */
    public function getPlaceholders(): array;

    public function getPlaceholderInfo(): string;

}
