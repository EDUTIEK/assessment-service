<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Api;

use Edutiek\AssessmentService\Views\Data\AssessmentViewRepo;
use Edutiek\AssessmentService\Views\Data\WriterViewRepo;
use Edutiek\AssessmentService\Views\Data\CorrectionsViewRepo;
use Edutiek\AssessmentService\Views\Data\StatisticViewRepo;

interface Dependencies
{
    public function assessment(): AssessmentViewRepo;
    public function writer(): WriterViewRepo;
    public function corrections(): CorrectionsViewRepo;
    public function statistic(): StatisticViewRepo;
}
