<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Notification;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Data\NotificationSettings;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private LanguageService $lang,
    ) {
    }

    private function defaultTemplate(NotificationType $type)
    {
        if (file_exists(__DIR__ . '/' . $type->value . '.txt')) {
            return file_get_contents(__DIR__ . '/' . $type->value . '.txt');
        }
        return null;
    }

    public function getSettings(NotificationType $type): NotificationSettings
    {
        return
            $this->repos->notificationSettings()->oneByAssIdAndType($this->ass_id, $type)
                ?? $this->repos->notificationSettings()->new()
                    ->setAssId($this->ass_id)
                    ->setType($type)
                    ->setActive(false)
                    ->setSubject($this->lang->txt($type->subjectLangVar()))
                    ->setBody($this->defaultTemplate($type));
    }

    public function allSettings(): array
    {
        $settings = [];
        foreach (NotificationType::availableTypes() as $type) {
            $settings[$type->value] = $this->getSettings($type);
        }
        return $settings;
    }

    public function usersByType(NotificationType $type): array
    {
        return $this->repos->notificationUser()->allByAssIdAndType($this->ass_id, $type);
    }

    public function queueByType(NotificationType $type): array
    {
        return $this->repos->notificationQueue()->allByAssIdAndType($this->ass_id, $type);
    }

    public function saveUsers(NotificationType $type, array $user_ids): void
    {
        $repo = $this->repos->notificationUser();
        $repo->deleteByAssIdAndType($this->ass_id, $type);
        foreach ($user_ids as $user_id) {
            $repo->save(
                $repo->new()
                ->setAssId($this->ass_id)
                ->setUserId($user_id)
                ->setType($type)
            );
        }
    }

    public function saveQueue(NotificationType $type, array $user_ids): void
    {
        $repo = $this->repos->notificationQueue();
        $repo->deleteByAssIdAndType($this->ass_id, $type);
        foreach ($user_ids as $user_id) {
            $repo->save(
                $repo->new()
                ->setAssId($this->ass_id)
                ->setUserId($user_id)
                ->setType($type)
                ->setAdded(new DateTimeImmutable())
            );
        }
    }

    public function sendFor(NotificationType $type, int $writer_id): void
    {
        // TODO: Implement sendFor() method.
    }

    /**
     * @return array placeholder => description
     */
    public function getPlaceholders(): array
    {
        return [];
    }

    private function fillPlaceholders(string $template, int $user_id, int $writer_id): string
    {
        return $template;
    }
}
