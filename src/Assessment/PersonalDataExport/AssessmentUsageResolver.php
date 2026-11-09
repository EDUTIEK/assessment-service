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
            $scopes[] = $this->createScope($query, $ass_id, null, $writer_id, $corrector_id);
        }

        return $scopes;
    }

    public function collectData(UsageScope $scope, array $sub_nodes): UsageNode
    {
        $assessment_contains_data = false;

        $assembly = new AssessmentGroupAssembly($scope);
        $assembly->setLocations($this->repositories->location()->allByAssId($scope->getAssId()));
        $assembly->setGrades($this->repositories->gradeLevel()->allByAssId($scope->getAssId()));

        if ($scope->isCorrector()) {
            $assembly->addCorrector(
                $this->repositories->corrector()->one($scope->getCorrectorId())
            );
            $assessment_contains_data = true;
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
            $assessment_contains_data = true;
        }

        $login = $this->user_service->getLoginByUserId($scope->getUserId());
        $log_entries = $this->repositories->logEntry()->allByAssIdAndLoginMentioned($scope->getAssId(), $login);
        if (!empty($log_entries)) {
            $assembly->addLogEntries($log_entries, $login);
            $assessment_contains_data = true;
        }

        $mentioned_writer = $this->repositories->writer()->allByAssIdAndUserIdMentioned($scope->getAssId(), $scope->getUserId());
        if (!empty($mentioned_writer)) {
            $assembly->addMentionsInWriter($mentioned_writer);
            $assessment_contains_data = true;
        }

        $final_nodes = [];
        foreach ($sub_nodes as $node) {
            foreach ($node->getGroupsForAssessment() as $group) {
                $assembly->addSubGroup($group);
                $assessment_contains_data = true;
            }
            foreach ($node->getGroupsFinal() as $group) {
                $final_nodes[] = $group;
            }
        }

        if($assessment_contains_data) {
            $assembly->addAssmentInfo(
                $this->repositories->orgaSettings()->one($scope->getAssId()),
                $this->repositories->properties()->one($scope->getAssId())
            );

            $assembly->addPermissions(
                $this->repositories->permissions()->allByAssIdAndUserId($scope->getAssId(), $scope->getUserId())
            );

            $final_nodes[] = $assembly->getGroup();
        }


        return $this->createNode($scope, $final_nodes, [], []);
    }
}
