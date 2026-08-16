<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\Writer;

interface ReadService
{
    /**
     * Get all entries with client data of a writer
     * An entry is created when the writer app is called with a new php session.
     * Some data (battery, hidden) is only available for the newest entry.
     * A session_id !== null indicates the newest entry.
     *
     * @return WriterClient[]
     */
    public function all(int $writer_id);

    /**
     * Get the entry that fits to the current writer web app
     * It is recognized by the id of the current authentication token
     */
    public function current(Writer $writer): ?WriterClient;
}
