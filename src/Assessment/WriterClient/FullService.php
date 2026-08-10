<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService extends ReadService
{
    public function create(Writer $writer): WriterClient;

    public function save(WriterClient $client): void;
}
