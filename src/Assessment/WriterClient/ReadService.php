<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\Writer;

interface ReadService
{
    /**
     * @param $writer_id
     * @return WriterClient[]
     */
    public function all(int $writer_id);

    public function current(Writer $writer): ?WriterClient;
}
