<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

class UsageNode
{
    /**
     * @param UsageScope $scope
     * @param Group[]    $groups_final
     * @param Group[]    $groups_for_assessment
     * @param Group[]    $groups_for_task
     */
    public function __construct(
        private readonly UsageScope $scope,
        private readonly array $groups_final,
        private readonly array $groups_for_assessment,
        private readonly array $groups_for_task,
    ) {
    }

    public function getScope(): UsageScope
    {
        return $this->scope;
    }

    /**
     * @return Group[]
     */
    public function getGroupsForAssessment(): array
    {
        return $this->groups_for_assessment;
    }

    public function getGroupsFinal(): array
    {
        return $this->groups_final;
    }

    /**
     * @return Group[]
     */
    public function getGroupsForTask(): array
    {
        return $this->groups_for_task;
    }
}