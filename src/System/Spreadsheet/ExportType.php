<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Spreadsheet;

use ILIAS\FileUpload\MimeType;

enum ExportType: string
{
    case CSV = 'csv';
    case EXCEL = 'xlsx';


    /**
     * Get the file extension
     */
    public function extension(): string
    {
        return match($this) {
            self::CSV => '.csv',
            self::EXCEL => '.xlsx'
        };
    }

    /**
     * Get the file mimetype
     */
    public function mimetype(): string
    {
        return match($this) {
            self::CSV => 'text/csv',
            self::EXCEL => 'application/excel'
        };
    }
}
