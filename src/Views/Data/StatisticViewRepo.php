<?php

namespace Edutiek\AssessmentService\Views\Data;

interface StatisticViewRepo
{
    public function oneCorrector(int $corrector_id, array $filter): StatisticView;

    /**
     * @param array    $filter
     * @return array<StatisticView, StatisticView[]>
     */
    public function someCorrections(array $filter): array;
    public function oneAssessment(int $ass_id, array $filter): StatisticView;

    /**
     * @param array    $filter
     * @return array{general:StatisticView, by_assessment:StatisticView[]}
     */
    public function someAssessments(array $filter): array;

    /**
     * @param array    $filter
     * @return array{general:StatisticView, by_user:StatisticView[]}
     */
    public function someWriter(array $filter): array;
}