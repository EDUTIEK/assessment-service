<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface NotificationSettingsRepo
{
    public function new(): NotificationSettings;
    public function oneByAssIdAndType(int $ass_id, NotificationType $type): ?NotificationSettings;
    /** @return NotificationSettings[] */
    public function allByAssId(int $ass_id): array;
    /** @return Alert[] */
    public function save(NotificationSettings $entity): void;

    public function deleteByAssId(int $ass_id): void;
}
