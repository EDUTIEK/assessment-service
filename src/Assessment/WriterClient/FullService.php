<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Data\WriterClient;

interface FullService extends ReadService
{
    public function save(WriterClient $client): void;
}