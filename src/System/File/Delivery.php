<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\File;

use Edutiek\AssessmentService\System\Data\FileInfo;

interface Delivery
{
    /**
     * Send a file which is stored with the given id
     */
    public function sendFile(string $id, Disposition $disposition): never;

    /**
     * Send data content as a file with the given disposition
     * Use the file name and mime type provided by the file info
     */
    public function sendData(string $data, Disposition $disposition, ?FileInfo $info): never;
}
