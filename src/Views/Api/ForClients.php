<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Api;

use Edutiek\AssessmentService\Views\Data\WriterViewRepo;
use Edutiek\AssessmentService\Views\Data\CorrectionsViewRepo;
use Edutiek\AssessmentService\Views\Api\Dependencies;
use Edutiek\AssessmentService\Views\Data\AssessmentViewRepo;

readonly class ForClients
{
    public function __construct(
        private Dependencies $dependencies,
    ) {
    }

    public function writer(): WriterViewRepo
    {
        return $this->dependencies->writer();
    }
    public function corrections(): CorrectionsViewRepo
    {
        return $this->dependencies->corrections();
    }
    public function statistic()
    {
        return $this->dependencies->statistic();
    }
}
