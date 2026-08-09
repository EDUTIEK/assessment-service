<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Data\WriterClient;

interface ReadService
{
    /**
     * @param $writer_id
     * @return WriterClient[]
     */
    public function all(int $writer_id);

    public function get(int $writer_id, int $token_id) : WriterClient;
}