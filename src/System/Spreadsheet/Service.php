<?php

namespace Edutiek\AssessmentService\System\Spreadsheet;

use Edutiek\AssessmentService\System\File\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

readonly class Service implements FullService
{
    public function __construct(
        private Storage $store
    ) {
    }

    /**
     * Get the data from a file as an array
     */
    public function dataFromFile(string $id): array
    {
        $path = $this->store->getReadablePath($id);
        if ($path) {
            return IOFactory::load($path)->getActiveSheet()->toArray();
        }
        return [];
    }
}
