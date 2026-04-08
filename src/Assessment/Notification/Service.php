<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Notification;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Data\NotificationSettings;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\Assessment\Data\NotificationUser;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\OrgaSettings\ReadService as OrgaSettings;
use Edutiek\AssessmentService\System\Config\CronJobId;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\Mail\Delivery as MailDelivery;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private OrgaSettings $orga,
        private LanguageService $lang,
        private MailDelivery $mail,
        private ConfigService $config,
        private UserReadService $users,
    ) {
    }

    private function defaultTemplate(NotificationType $type)
    {
        if (file_exists(__DIR__ . '/templates/' . $type->value . '.txt')) {
            return file_get_contents(__DIR__ . '/templates/' . $type->value . '.txt');
        }
        return null;
    }

    public function getSettings(NotificationType $type): NotificationSettings
    {
        // use clone because a save chechs the sttings before
        return clone($this->repos->notificationSettings()->oneByAssIdAndType($this->ass_id, $type)
            ?? $this->repos->notificationSettings()->new()
                ->setAssId($this->ass_id)
                ->setType($type)
                ->setActive(false)
                ->setSubject($this->lang->txt($type->subjectLangVar()))
                ->setBody($this->defaultTemplate($type))
        );
    }

    public function newSettings(): NotificationSettings
    {
        return $this->repos->notificationSettings()->new();
    }

    public function allSettings(): array
    {
        $settings = [];
        foreach (NotificationType::availableTypes() as $type) {
            $settings[$type->value] = $this->getSettings($type);
        }
        return $settings;
    }

    public function saveSettings(NotificationSettings $settings): void
    {
        $existing = $this->getSettings($settings->getType());
        $this->repos->notificationSettings()->save($settings);

        if ($existing->getActive() && !$settings->getActive()) {
            $this->repos->notificationQueue()->deleteByAssIdAndType($this->ass_id, $settings->getType());
        }
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

    public function createFor(NotificationType $type, ?Writer $writer): void
    {
        $setting = $this->getSettings($type);
        if (!$setting->getActive()) {
            return;
        }

        $to_ids = [];
        switch ($type) {
            case NotificationType::WRITER_CORRECTION_FINALIZED:
                if ($writer !== null) {
                    if ($this->orga->reviewPossible()) {
                        $to_ids[] = $writer->getUserId();
                    } elseif ($this->orga->get()->getReviewEnabled()) {
                        if ($this->config->getSetup()->isCronJobActive(CronJobId::REVIEW_NOTIFICATION)) {
                            $queue = $this->repos->notificationQueue()->new()
                                ->setAssId($this->ass_id)
                                ->setType(NotificationType::WRITER_CORRECTION_FINALIZED)
                                ->setUserId($writer->getUserId())
                                ->setAdded(new DateTimeImmutable());
                            $this->repos->notificationQueue()->save($queue);
                            return;
                        }
                    }
                }
                break;
            case NotificationType::ADMIN_STITCH_NEEDED:
            case NotificationType::ADMIN_WRITING_AUTHORIZED:
                $to_ids = array_map(
                    fn(NotificationUser $user) => $user->getUserId(),
                    $this->repos->notificationUser()->allByAssIdAndType($this->ass_id, $type)
                );
        }

        $this->sendDirect($type, $to_ids, $writer);
    }

    /**
     * @param int[] $to_ids
     */
    public function sendDirect(NotificationType $type, array $to_ids, ?Writer $writer): void
    {
        $setting = $this->getSettings($type);

        foreach ($to_ids as $to_id) {
            $subject = $this->fillPlaceholders($setting->getSubject(), $type, $to_id, $writer);
            $body = $this->fillPlaceholders($setting->getBody(), $type, $to_id, $writer);
            $this->mail->deliver($subject, $body, [$to_id]);
        }
    }

    /**
     * Get the info about available placeholders for the given type
     * @param NotificationType $type
     * @return string
     */
    public function getPlaceholderInfo(NotificationType $type): string
    {
        $lines = [];
        foreach ($type->placeholders() as $key => $var) {
            $lines[] = '<strong>[' . $key . ']</strong>: ' . $this->lang->txt($var);
        }
        return implode("\n", $lines);
    }

    private function fillPlaceholders(string $template, NotificationType $type, int $user_id, ?Writer $writer): string
    {
        $user_data = $this->users->getUser($user_id);
        $writer_data = $this->users->getUser($writer?->getUserId() ?? 0);

        foreach ($type->placeholders() as $key => $var) {
            $search = '[' . $key . ']';
            $replace = match($key) {
                'title' => $user_data?->getTitle(),
                'lastname' => $user_data?->getLastname(),
                'firstname' => $user_data?->getFirstname(),
                'fullname' => $user_data?->getFullname(false),
                'writer_login' => $writer_data?->getLogin(),
                'writer_name' => $writer_data?->getFullname(false),
                'writer_pseudonym' => $writer->getPseudonym(),
                'assessment_title' => $this->repos->properties()->one($this->ass_id)?->getTitle(),
                'assessment_link' => $this->repos->contextInfo()->link($this->ass_id, $user_id),
            };

            $template = str_replace($search, $replace ?? $search, $template);
        }

        return $template;
    }
}
