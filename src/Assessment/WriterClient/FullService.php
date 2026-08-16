<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService extends ReadService
{
    /**
     * Create a new entry with client data of a writer
     * Called when a web app is opened
     * - An entry with the current php session id is re-created
     * - The session, id, battery, and hidden status are set to null in older entries
     */
    public function create(Writer $writer): WriterClient;

    /**
     * Save a new client data entry
     * Replace an existing one (identified by writer and token id)
     * A scope check is done if the writer belongs to the assessment of the service
     */
    public function save(WriterClient $client): void;
}
