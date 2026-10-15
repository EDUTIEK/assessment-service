<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageResolver;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\Text;

abstract class AbstractUsageResolver implements UsageResolver
{
    public function collectUsage(UsageQuery $query): array
    {
        $nodes = [];
        $next_steps = $query->getNextSteps();

        foreach ($this->resolveUsages($query) as $scope) {
            $sub_nodes = [];
            $next_query = $this->nextQuery($query, $scope);

            foreach ($next_steps as $next_resolver) {
                foreach ($next_resolver->collectUsage($next_query) as $node) {
                    $sub_nodes[] = $node;
                }
            }

            $nodes[] = $this->collectData($scope, $sub_nodes);
        }
        return $nodes;
    }

    protected function nextQuery(UsageQuery $current_query, UsageScope $current_scope)
    {
        return $current_query->nextQuery(
            $current_scope->getAssId(),
            $current_scope->getTaskId(),
            $current_scope->getWriterId(),
            $current_scope->getCorrectorId(),
            $current_scope->isUserIdUsed()
        );
    }

    protected function createScope(
        UsageQuery $current_query,
        ?int $ass_id = null,
        ?int $task_id = null,
        ?int $writer_id = null,
        ?int $corrector_id = null,
        bool $is_user_id_used = false
    ): UsageScope {
        return new UsageScope(
            $this->componentId(),
            $current_query->getUserId(),
            $ass_id ?? $current_query->getAssId(),
            $task_id ?? $current_query->getTaskId(),
            $writer_id ?? $current_query->getWriterId(),
            $corrector_id ?? $current_query->getCorrectorId(),
            $is_user_id_used || $current_query->isUserIdUsedUpstream()
        );
    }

    protected function createNode(
        UsageScope $scope,
        array $groups_final,
        array $groups_for_assessment,
        array $groups_for_task
    ): UsageNode {
        return new UsageNode(
            $scope,
            $groups_final,
            $groups_for_assessment,
            $groups_for_task
        );
    }
}
