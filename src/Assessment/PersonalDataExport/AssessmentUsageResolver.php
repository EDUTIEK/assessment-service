<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\System\User\ReadService as UserService;

class AssessmentUsageResolver extends AbstractUsageResolver
{
    /**
     * @var Writer[]
     */
    private array $writer_by_ass_id = [];
    /**
     * @var Corrector[]
     */
    private array $corrector_by_ass_id = [];
    /**
     * @var OrgaSettings[]
     */
    private array $orga_settings_by_ass_id = [];

    public function __construct(private Repositories $repositories, private UserService $user_service)
    {
    }

    public function componentId(): string
    {
        return "Assessment";
    }

    public function resolveUsages(UsageQuery $query): array
    {
        $scopes = [];

        foreach ($this->repositories->writer()->allByUserId($query->getUserId()) as $writer) {
            $this->writer_by_ass_id[$writer->getAssId()] = $writer;
        }

        foreach ($this->repositories->corrector()->allByUserId($query->getUserId()) as $corrector) {
            $this->corrector_by_ass_id[$corrector->getAssId()] = $corrector;
        }

        foreach ($this->repositories->orgaSettings()->all() as $orga_setting) {
            $ass_id = $orga_setting->getAssId();
            $writer = $this->writer_by_ass_id[$ass_id] ?? null;
            $writer_id = $writer?->getId() ?? null;
            $corrector_id = ($this->corrector_by_ass_id[$ass_id] ?? null)?->getId() ?? null;
            $user_id_used = false;

            $this->repositories->token()->oneByIdsAndPurpose($ass_id, $query->getUserId(), TokenPurpose::DATA);
            $this->repositories->token()->oneByIdsAndPurpose($ass_id, $query->getUserId(), TokenPurpose::FILE);

            $login = $this->user_service->getLoginByUserId($query->getUserId());
            $log_entries = $this->repositories->logEntry()->allByAssIdAndLoginMentioned($ass_id, $login);

            if (!empty($log_entries)) {
                $user_id_used = true;
            }

            $mentioned_writer = $this->repositories->writer()->allByAssIdAndUserIdMentioned($ass_id, $query->getUserId());

            if (!empty($mentioned_writer)) {
                $user_id_used = true;
            }

            $token = $this->repositories->token()->allByUserIdAndAssId($query->getUserId(), $ass_id);
            if (!empty($token)) {
                $user_id_used = true;
            }

            // NotificationQueue (Technical)
            // NotificationUser (Technical)

            $scopes[] = $this->createScope($query, $ass_id, null, $writer_id, $corrector_id);
        }

        return $scopes;
    }

    public function collectData(UsageScope $scope, array $sub_nodes): UsageNode
    {
        $assembly = new AssessmentGroupAssembly($scope);
        $assembly->setLocations($this->repositories->location()->allByAssId($scope->getAssId()));
        $assembly->setGrades($this->repositories->gradeLevel()->allByAssId($scope->getAssId()));

        $assembly->addAssmentInfo(
            $this->repositories->orgaSettings()->one($scope->getAssId()),
            $this->repositories->properties()->one($scope->getAssId())
        );

        if ($scope->isCorrector()) {
            $assembly->addCorrector(
                $this->repositories->corrector()->one($scope->getCorrectorId())
            );
        }

        if ($scope->isWriter()) {
            $assembly->addWriter(
                $this->repositories->writer()->one($scope->getWriterId())
            );
            $writer_clients = $this->repositories->writerClient()->allByWriterId($scope->getWriterId());
            if (!empty($writer_clients)) {
                $assembly->addWriterClients($writer_clients);
            }

            $alerts = $this->repositories->alert()->allByAssIdAndWriterId($scope->getAssId(), $scope->getWriterId());
            if (!empty($alerts)) {
                $assembly->addAlerts($alerts);
            }
        }

        $login = $this->user_service->getLoginByUserId($scope->getUserId());
        $log_entries = $this->repositories->logEntry()->allByAssIdAndLoginMentioned($scope->getAssId(), $login);
        if (!empty($log_entries)) {
            $assembly->addLogEntries($log_entries, $login);
        }

        $assembly->addPermissions(
            $this->repositories->permissions()->allByAssIdAndUserId($scope->getAssId(), $scope->getUserId())
        );

        $mentioned_writer = $this->repositories->writer()->allByAssIdAndUserIdMentioned($scope->getAssId(), $scope->getUserId());
        if (!empty($mentioned_writer)) {
            $assembly->addMentionsInWriter($mentioned_writer);
        }

        $token = $this->repositories->token()->allByUserIdAndAssId($scope->getUserId(), $scope->getAssId());
        if (!empty($token)) {
            $assembly->addTokens($token);
        }

        $final_nodes = [];
        foreach ($sub_nodes as $node) {
            foreach ($node->getGroupsForAssessment() as $group) {
                $assembly->addSubGroup($group);
            }
            foreach ($node->getGroupsFinal() as $group) {
                $final_nodes[] = $group;
            }
        }

        return new UsageNode($scope, array_merge([$assembly->getGroup()], $final_nodes), [], []);
        //NotificationQueue W C A uid aid // Technical
        //NotificationUser W C A uid aid // Technical
    }
}
