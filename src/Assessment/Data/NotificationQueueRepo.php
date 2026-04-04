<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface NotificationQueueRepo
{
    public function new(): NotificationQueue;
    /** @return NotificationQueue[] */
    public function allByType(NotificationType $type): array;
    /** @return NotificationQueue[] */
    public function allByAssIdAndType(int $ass_id, NotificationType $type): array;
    public function save(NotificationQueue $entity): void;
    public function delete(NotificationQueue $entity): void;
    public function deleteByAssId(int $ass_id);
    public function deleteByAssIdAndType(int $ass_id, NotificationType $type);
    public function deleteByUserId(int $user_id);
}
