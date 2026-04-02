<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface NotificationUserRepo
{
    public function new(): NotificationUser;
    /** @return NotificationUser[] */
    public function allByAssIdAndType(int $ass_id, NotificationType $type): array;
    public function save(NotificationUser $entity): void;
    public function deleteByAssIdAndType(int $ass_id, NotificationType $type);
    public function deleteByAssId(int $ass_id);
    public function deleteByUserId(int $user_id);
}
