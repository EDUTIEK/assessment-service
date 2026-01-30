<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Api;

use Edutiek\AssessmentService\Views\Data\WriterViewRepo;
use Edutiek\AssessmentService\Views\Data\CorrectionsViewRepo;

interface ForClients
{
    public function writer(): WriterViewRepo;
    public function corrections(): CorrectionsViewRepo;
    public function statistic();
}