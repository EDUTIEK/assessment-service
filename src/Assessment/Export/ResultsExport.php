<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheets;
use Edutiek\AssessmentService\Assessment\Data\ResultExportFormat;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;

readonly class ResultsExport
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private Spreadsheets $spreadsheets
    ) {
    }


    public function create(): string
    {
        $settings = $this->repos->exportSettings()->one($this->ass_id) ?? $this->repos->exportSettings()->new();
        switch ($settings->getResultExportFormat()) {
            case ResultExportFormat::EDUTIEK:
                return $this->createEdutiekExport();
            case ResultExportFormat::EXAMIS:
                return $this->createExamisExport();
            case ResultExportFormat::JUSTA:
                return $this->createJustaExport();
        }
        return '';
    }

    private function createEdutiekExport()
    {

        $header = [
            'login' => 'Login'
        ];

        $rows = [];
        $rows[] = [
            'login' => 'root'
        ];
        return $this->spreadsheets->dataToFile($header, $rows, ExportType::CSV, 'Ergebnisse');
    }

    private function createJustaExport()
    {
        return '';
    }

    private function createExamisExport()
    {
        return '';
    }
}
