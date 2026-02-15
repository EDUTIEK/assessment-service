<?php

namespace Edutiek\AssessmentService\Views\Data;

interface StatisticViewRepo
{
    /**
     * @param array $filter
     * @return StatisticView
     */
    public function someAssessments(array $filter): StatisticView;

    /**
     * @param array $filter
     * @return StatisticView
     */
    public function someCorrections(array $filter): StatisticView;

}