<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSnippets;

use Edutiek\AssessmentService\Task\Data\CorrectorSnippetPurpose;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheets;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private Spreadsheets $spreadsheets,
        private Storage $storage,
        private Language $language,
    ) {
    }


    public function export(int $corrector_id, CorrectorSnippetPurpose $purpose): FileInfo
    {
        $header = [
            'text' => 'text',
            'shortcut' => 'shortcut',
        ];

        $rows = [];
        foreach ($this->repos->correctorSnippets()->allByCorrectorId($this->ass_id, $corrector_id) as $snippet) {
            if ($snippet->getPurpose() === $purpose) {
                $rows[] = [
                    'text' => $snippet->getText(),
                    'shortcut' => $snippet->getShortcut()
                ];
            }
        }

        $id = $this->spreadsheets->dataToFile(
            $header,
            $rows,
            ExportType::EXCEL,
            $this->language->txt('snippets_export_filename')
        );

        return $this->storage->getFileInfo($id)->setDisposable(true);
    }
}
