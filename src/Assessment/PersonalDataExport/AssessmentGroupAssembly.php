<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\Data\Alert;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\LogEntry;
use Edutiek\AssessmentService\Assessment\Data\NotificationQueue;
use Edutiek\AssessmentService\Assessment\Data\NotificationUser;
use Edutiek\AssessmentService\Assessment\Data\Token;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\Permissions;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\Properties;
use Edutiek\AssessmentService\Assessment\Data\Location;

class AssessmentGroupAssembly extends AbstractGroupAssembly
{
    /**
     * @var Location
     */
    private array $locations = [];
    private array $grades = [];

    public function createGroup(): Group
    {
        return new Group("Assessment ". $this->usage_scope->getAssId());
    }

    /**
     * @param Location[] $locations
     * @return void
     */
    public function setLocations(array $locations)
    {
        foreach($locations as $location) {
            $this->locations[$location->getId()] = $location;
        }
    }

    public function setGrades(array $grades)
    {
        foreach($grades as $grade) {
            $this->grades[$grade->getId()] = $grade;
        }
    }

    /**
     * @param OrgaSettings $settings
     * @param Properties   $properties
     * @return void
     */
    public function addAssmentInfo(OrgaSettings $settings, Properties $properties): void
    {
        $this->addOrgaSettingsToGroup($this->group, $settings);
        $this->addPropertiesToGroup($this->group, $properties);
    }

    /**
     * @param Alert[] $alerts
     * @return void
     */
    public function addAlerts(array $alerts): void
    {
        $alert_group = new Group("Alert");
        /**
         * @var Alert $alert
         */
        foreach ($alerts as $alert) {
            $sub_group = new Group((string) $alert->getId());
            $this->addAlertToGroup($sub_group, $alert);
            $alert_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($alert_group);
    }

    public function addCorrector(Corrector $corrector): void
    {
        $corrector_group = new Group("Corrector");
        $this->addCorrectorToGroup($corrector_group, $corrector);
        $this->group->addSubGroup($corrector_group);
    }

    /**
     * @param LogEntry[] $log_entries
     * @return void
     */
    public function addLogEntries(array $log_entries, string $login): void
    {
        $log_entries_group = new Group("Log Entry");
        /**
         * @var LogEntry $log_entry
         */
        foreach ($log_entries as $log_entry) {
            $sub_group = new Group((string) $log_entry->getId());
            $this->addLogEntryToGroup($sub_group, $log_entry, $login);
            $log_entries_group->addSubGroup($sub_group);
        }

        $this->group->addSubGroup($log_entries_group);
    }

    /**
     * @param NotificationQueue[] $notification_queues
     * @return void
     */
    public function addNotificationQueues(array $notification_queues): void
    {
        $notification_queues_group = new Group("Notification Queue");
        /**
         * @var NotificationQueue $notification_queue
         */
        foreach ($notification_queues as $notification_queue) {
            $sub_group = new Group((string) $notification_queue->getId());
            $this->addNotificationQueueToGroup($sub_group, $notification_queue);
            $notification_queues_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($notification_queues_group);
    }
    /**
     * @param NotificationUser[] $notification_users
     * @return void
     */
    public function addNotificationUsers(array $notification_users): void
    {
        $notification_users_group = new Group("Notification User");
        /**
         * @var NotificationUser $notification_user
         */
        foreach ($notification_users as $notification_user) {
            $sub_group = new Group((string) $notification_user->getId());
            $this->addNotificationUserToGroup($sub_group, $notification_user);
            $notification_users_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($notification_users_group);
    }

    /**
     * @param Token[] $tokens
     * @return void
     */
    public function addTokens(array $tokens): void
    {
        $tokens_group = new Group("Token");
        /**
         * @var Token $token
         */
        foreach ($tokens as $token) {
            $sub_group = new Group((string) $token->getId());
            $this->addTokenToGroup($sub_group, $token);
            $tokens_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($tokens_group);
    }

    /**
     * @param Writer[] $writers
     * @return void
     */
    public function addWriter(Writer $writer): void
    {
        $writer_group = new Group("Writer");
        $this->addWriterToGroup($writer_group, $writer);
        $this->group->addSubGroup($writer_group);
        $writing_group = new Group("Writing");
        $this->addWritingToGroup($writing_group, $writer);
        $this->group->addSubGroup($writing_group);
    }

    /**
     * @param WriterClient[] $writer_clients
     * @return void
     */
    public function addWriterClients(array $writer_clients): void
    {
        $writer_clients_group = new Group("Writer Client");
        /**
         * @var WriterClient $writer_client
         */
        foreach ($writer_clients as $writer_client) {
            $sub_group = new Group((string) $writer_client->getWriterId());
            $this->addWriterClientToGroup($sub_group, $writer_client);
            $writer_clients_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($writer_clients_group);
    }

    /**
     * @param Permissions[] $permissions
     * @return void
     */
    public function addPermissions(array $permissions): void
    {
        $permissions_group = new Group("Permissions");
        /**
         * @var Permissions $permission
         */
        foreach($permissions as $permission) {
            $sub_group = new Group((string) $permission->getContextId());
            $this->addPermissionToGroup($sub_group, $permission);
            $permissions_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($permissions_group);
    }

    /**
     * @param Writer[] $writer
     * @return void
     */
    public function addMentionsInWriter(array $writers): void
    {
        $mentions_group = new Group("Mentioned in Writing Procedures");
        foreach($writers as $writer) {
            $sub_group = new Group("Writing " . $writer->getId());
            $this->addMentionInWriterToGroup($sub_group, $writer);
            $mentions_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($mentions_group);
    }

    private function addOrgaSettingsToGroup(Group $group, OrgaSettings $orga_settings): void
    {
        $group->addAttribute($this->attribute_factory->number("Id", $orga_settings->getAssId()));
        if ($orga_settings->getDescription() !== null) {
            $group->addAttribute($this->attribute_factory->text("Description", $orga_settings->getDescription()));
        }

        if($this->usage_scope->isWriter()) {
            if ($orga_settings->getWritingStart() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Writing Start", $orga_settings->getWritingStart()));
            }
            if ($orga_settings->getWritingEnd() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Writing End", $orga_settings->getWritingEnd()));
            }
            if ($orga_settings->getWritingLimitMinutes() !== null) {
                $group->addAttribute($this->attribute_factory->number("Writing limit Minutes", $orga_settings->getWritingLimitMinutes()));
            }
        }

        if($this->usage_scope->isWriter() || $this->usage_scope->isCorrector()) {
            $group->addAttribute($this->attribute_factory->boolean("Review Enabled", $orga_settings->getReviewEnabled()));
            if ($orga_settings->getReviewStart() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Review Start", $orga_settings->getReviewStart()));
            }
            if ($orga_settings->getReviewEnd() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Review End", $orga_settings->getReviewEnd()));
            }
            if ($orga_settings->getSolutionAvailableDate() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Solution Available", $orga_settings->getSolutionAvailableDate()));
            }
        }

        if($this->usage_scope->isCorrector()) {
            if ($orga_settings->getCorrectionStart() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Correction Start", $orga_settings->getCorrectionStart()));
            }
            if ($orga_settings->getCorrectionEnd() !== null) {
                $group->addAttribute($this->attribute_factory->dateTime("Correction End", $orga_settings->getCorrectionEnd()));
            }
        }
    }

    private function addPropertiesToGroup(Group $group, Properties $properties): void
    {
        $group->addAttribute($this->attribute_factory->text("Title", $properties->getTitle()));
        $group->addAttribute($this->attribute_factory->text("Description", $properties->getDescription()));
    }

    private function addAlertToGroup(Group $group, Alert $alert): void
    {
        if ($alert->getTitle() !== null) {
            $group->addAttribute($this->attribute_factory->text("Title", $alert->getTitle()));
        }
        $group->addAttribute($this->attribute_factory->text("Message", $alert->getMessage()));
        if ($alert->getShownFrom() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Shown From", $alert->getShownFrom()));
        }
        if ($alert->getShownUntil() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Shown Until", $alert->getShownUntil()));
        }
    }

    private function addCorrectorToGroup(Group $group, Corrector $corrector): void
    {
        $group->addAttribute($this->attribute_factory->number("Id", $corrector->getId()));
        $group->addAttribute($this->attribute_factory->number("User Id", $corrector->getUserId()));
        if ($corrector->getCorrectionReport() !== null) {
            $group->addAttribute($this->attribute_factory->text("Correction Report", $corrector->getCorrectionReport()));
        }
    }

    private function addLogEntryToGroup(Group $group, LogEntry $log_entry, string $login): void
    {
        if ($log_entry->getEntry() !== null) {
            $group->addAttribute($this->attribute_factory->text("Entry", $this->anonymizeLogEntry($log_entry->getEntry(), $login)));
        }
        if ($log_entry->getTimestamp() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Timestamp", $log_entry->getTimestamp()));
        }
        $group->addAttribute($this->attribute_factory->text("Category", $log_entry->getCategory()->value));
    }

    private function addNotificationQueueToGroup(Group $group, NotificationQueue $notification_queue): void
    {
        #$group->addAttribute($this->attribute_factory->number("Ass Id", $notification_queue->getAssId()));
        $group->addAttribute($this->attribute_factory->number("User Id", $notification_queue->getUserId()));
        $group->addAttribute($this->attribute_factory->text("Type", $notification_queue->getType()->value));
        if ($notification_queue->getAdded() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Added", $notification_queue->getAdded()));
        }
    }

    private function addNotificationUserToGroup(Group $group, NotificationUser $notification_user): void
    {
        #$group->addAttribute($this->attribute_factory->number("Ass Id", $notification_user->getAssId()));
        $group->addAttribute($this->attribute_factory->number("User Id", $notification_user->getUserId()));
        $group->addAttribute($this->attribute_factory->text("Type", $notification_user->getType()->value));
    }

    private function addTokenToGroup(Group $group, Token $token): void
    {
        #$group->addAttribute($this->attribute_factory->number("Ass Id", $token->getAssId()));
        $group->addAttribute($this->attribute_factory->number("User Id", $token->getUserId()));
        $group->addAttribute($this->attribute_factory->text("Token", $token->getToken()));
        $group->addAttribute($this->attribute_factory->text("Ip", $token->getIp()));
        $group->addAttribute($this->attribute_factory->text("Purpose", $token->getPurpose()->value));
        if ($token->getValidUntil() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Valid Until", $token->getValidUntil()));
        }
    }

    private function addWriterToGroup(Group $group, Writer $writer): void
    {
        $group->addAttribute($this->attribute_factory->number("User Id", $writer->getUserId()));
        $group->addAttribute($this->attribute_factory->text("Pseudonym", $writer->getPseudonym()));
        if($writer->getLocation() !== null) {
            $location_id = $writer->getLocation();
            $group->addAttribute($this->attribute_factory->text("Location", $this->locations[$location_id]?->getTitle() ?? ("Id " . $location_id)));
        }
    }

    private function addWritingToGroup(Group $group, Writer $writer): void
    {
        if ($writer->getEarliestStart() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Earliest Start", $writer->getEarliestStart()));
        }
        if ($writer->getLatestEnd() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Latest End", $writer->getLatestEnd()));
        }
        if ($writer->getTimeLimitMinutes() !== null) {
            $group->addAttribute($this->attribute_factory->number("Time Limit Minutes", $writer->getTimeLimitMinutes()));
        }
        if ($writer->getWorkingStart() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Working Start", $writer->getWorkingStart()));
        }
        if ($writer->getFinalPoints() !== null) {
            $group->addAttribute($this->attribute_factory->number("Final Points", $writer->getFinalPoints()));
        }
        if($writer->getFinalGradeLevelId() !== null) {
            $grade_id = $writer->getFinalGradeLevelId();
            $grade = $this->grades[$grade_id];
            $group->addAttribute($this->attribute_factory->text("Grade Level",  $grade?->getGrade() ?? ("Id" . $grade_id)));
            if($grade !== null) {
                $group->addAttribute($this->attribute_factory->boolean("Passed",  $grade->getPassed()));
            }
        }

        $status = fn(int $i) => match($i){
            1 => "Writing Excluded",
            2 => "Writing Authorized",
            3 => "Writing Started",
            4 => "Writing not Started",
            5 => "Open",
            6 => "Stitch Needed",
            7 => "Finalized",
            8 => "Approximation",
            9 => "Consulting",
            default => "unknown"
        };

        $group->addAttribute($this->attribute_factory->text("Writing Status", $status($writer->getWritingStatus()->value)));
        $group->addAttribute($this->attribute_factory->text("Correction Status", $writer->getCorrectionStatus()->value));
        $group->addAttribute($this->attribute_factory->text("Combined Status", $status($writer->getCombinedStatus()->value)));
    }

    private function addMentionInWriterToGroup(Group $group, Writer $writer): void
    {
        $group->addAttribute($this->attribute_factory->text("User Id", $this->anonymizeUser($writer->getUserId())));
        if($writer->getWritingAuthorizedBy() === $this->usage_scope->getUserId()) {
            $group->addAttribute($this->attribute_factory->text("Writing Authorized By", $this->anonymizeUser($writer->getWritingAuthorizedBy())));
            $group->addAttribute($this->attribute_factory->dateTime("Writing Authorized On", $writer->getWritingAuthorized()));
        }

        if($writer->getWritingExcludedBy() === $this->usage_scope->getUserId()) {
            $group->addAttribute($this->attribute_factory->text("Writing Excluded By", $this->anonymizeUser($writer->getWritingExcludedBy())));
            $group->addAttribute($this->attribute_factory->dateTime("Writing Excluded On", $writer->getWritingExcluded()));
        }

        if($writer->getCorrectionFinalizedBy() === $this->usage_scope->getUserId()) {
            $group->addAttribute($this->attribute_factory->text("Correction Finalized By", $this->anonymizeUser($writer->getCorrectionFinalizedBy())));
            $group->addAttribute($this->attribute_factory->dateTime("Correction Finalized On", $writer->getCorrectionFinalized()));
        }
    }


    private function addWriterClientToGroup(Group $group, WriterClient $writer_client): void
    {
        $group->addAttribute($this->attribute_factory->number("Token Id", $writer_client->getTokenId()));
        if ($writer_client->getSessionId() !== null) {
            $group->addAttribute($this->attribute_factory->text("Session Id", $writer_client->getSessionId()));
        }
        $group->addAttribute($this->attribute_factory->dateTime("First Access", $writer_client->getFirstAccess()));
        $group->addAttribute($this->attribute_factory->dateTime("Last Access", $writer_client->getLastAccess()));
        if ($writer_client->getIp() !== null) {
            $group->addAttribute($this->attribute_factory->text("Ip", $writer_client->getIp()));
        }
        if ($writer_client->getUserAgent() !== null) {
            $group->addAttribute($this->attribute_factory->text("User Agent", $writer_client->getUserAgent()));
        }
        if ($writer_client->getPlatform() !== null) {
            $group->addAttribute($this->attribute_factory->text("Platform", $writer_client->getPlatform()));
        }
        if ($writer_client->getBattery() !== null) {
            $group->addAttribute($this->attribute_factory->number("Battery", $writer_client->getBattery()));
        }
        if ($writer_client->getHidden() !== null) {
            $group->addAttribute($this->attribute_factory->boolean("Hidden", $writer_client->getHidden()));
        }
    }

    private function addPermissionToGroup(Group $group, Permissions $permission): void
    {
        $group->addAttribute($this->attribute_factory->boolean("Maintain Content", $permission->getMaintainContent()));
        $group->addAttribute($this->attribute_factory->boolean("Maintain Correction", $permission->getMaintainCorrection()));
        $group->addAttribute($this->attribute_factory->boolean("Maintain Settings", $permission->getMaintainSettings()));
        $group->addAttribute($this->attribute_factory->boolean("Maintain Writing", $permission->getMaintainWriting()));
        $group->addAttribute($this->attribute_factory->boolean("Proctor Writing", $permission->getProctorWriting()));
        $group->addAttribute($this->attribute_factory->boolean("Read", $permission->getRead()));
        $group->addAttribute($this->attribute_factory->boolean("Visible", $permission->getVisible()));
        $group->addAttribute($this->attribute_factory->number("Context Id", $permission->getContextId()));
    }

    private static function anonymizeLogEntry(string $log_entry, string $subject_login, bool $pseudonymize = true): string
    {
        $PATTERN_NEW = '/«([^«»]+)\[([^\]«»]+)\]»/u';

        /** Old format: anything up to [login], no guillemets (title-less match) */
        $PATTERN_OLD = '/(?<!«)([A-ZÄÖÜ][\p{L}\-\'’]*(?:\s+[A-ZÄÖÜ][\p{L}\-\'’]+)*'
        . '(?:\s*,\s*[A-ZÄÖÜ][\p{L}\-\'’]*)?)\s*\[([^\]«»]+)\]/u';

        $result = preg_replace_callback(
            $PATTERN_NEW,
            fn (array $m) => self::replaceLogin($m, $subject_login, $pseudonymize),
            $log_entry
        );

        return preg_replace_callback(
            $PATTERN_OLD,
            fn (array $m) => self::replaceLogin($m, $subject_login, $pseudonymize),
            $result
        );
    }

    private static function replaceLogin(array $m, string $subject_login, bool $pseudonymize): string
    {
        $login = trim($m[2]);

        if (self::loginEquals($login, $subject_login)) {
            return $m[0];
        }

        if ($pseudonymize) {
            $hash = substr(sha1('gdpr-anon:' . mb_strtolower($login)), 0, 8);
            return '«User ' . $hash . '»';
        }

        return '«anonymized user»';
    }

    private static function loginEquals(string $a, string $b): bool
    {
        $clean = fn (string $s) => preg_replace('/\s+/u', '', mb_strtolower(trim($s)));
        return $clean($a) === $clean($b);
    }
}
