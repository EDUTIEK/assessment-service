<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheet;

readonly class ImportTypeBavaria implements ImportType
{
    private const FILE_PATTERN = '/^[[:alnum:]]+-\d+_([[:alpha:]]-\d+)_.*.pdf$/';
    private const PROTOCOL_FILE_NAME = 'Abgabe-Protokoll.xlsx';

    /**
     * column key => header langguage variable
     */
    private const CSV_MAP = [
        'id' => 'by_csv_id',
        'alloc_time' => 'by_csv_alloc_time',
        'used_time' => 'by_csv_used_time',
        'logged_in' => 'by_csv_logged_in',
        'given_up' => 'by_csv_given_up',
        'pdf_hash' => 'by_csv_pdf_hash',
    ];

    public function __construct(
        private Spreadsheet $spreadsheet,
        private Language $lng
    ) {
    }

    public function detectByFilenames(array $filenames): bool
    {
        return in_array(self::PROTOCOL_FILE_NAME, $filenames)
            && !empty(array_filter(
                $filenames,
                fn($file) => preg_match(self::FILE_PATTERN, $file)
            ));
    }

    public function assignFiles(array $files): ImportResult
    {
        $found = array_filter($files, fn($file) => $file->getFileName() == self::PROTOCOL_FILE_NAME);
        $protocol = $this->readProtocol(current($found)->getTempId() ?? '');

        $assigned_ids = [];
        foreach ($files as $file) {
            $id = preg_match(self::FILE_PATTERN, $file->getFileName(), $matches) ? $matches[1] : null;
            $data = $protocol[$id] ?? null;
            if ($data) {
                if (in_array($id, $assigned_ids)) {
                    return (new ImportResult())
                        ->add(false, sprintf($this->lng->txt('import_double_id_match'), $id));
                }
                $assigned_ids[] = $id;
                $file->setRelevant(true)
                    ->setLogin($this->loginName($id))
                    ->setHashOk($file->getHash() === ($data['pdf_hash'] ?? ''));

                if (!$file->isHashOk()) {
                    $file->addError($this->lng->txt('import_wrong_hash'));
                }
            }
        }

        $missing_ids = array_diff(array_keys($protocol), $assigned_ids);
        if (!empty($missing_ids)) {
            return (new ImportResult())
                ->add(false, sprintf(
                    $this->lng->txt('import_missing_files_for'),
                    implode(', ', $missing_ids)
                ));
        }

        return new ImportResult(true);
    }

    public function columns(): array
    {
        return [
            'file' => new Column(Column::BOOLEAN, $this->lng->txt('essay_import_column_file')),
            'user' => new Column(Column::TEXT, $this->lng->txt('essay_import_column_user')),
            'id' => new Column(Column::TEXT, $this->lng->txt('essay_import_column_id')),
            'hash_ok' => new Column(Column::BOOLEAN, $this->lng->txt('essay_import_column_hash_ok')),
            'import_possible' => new Column(Column::BOOLEAN, $this->lng->txt('essay_import_column_import_possible')),
            'comment' => new Column(Column::TEXT, $this->lng->txt('essay_import_column_comment')),
        ];
    }

    public function rows(array $files): array
    {
        $rows = [];
        foreach ($files as $file) {
            $rows[] = new Row($file->getTempId(), [
                'file' => $file->getFileName(),
                'user' => $file->getLogin(),
                'id' => $file->getId(),
                'hash_ok' => $file->isHashOk(),
                'import_possible' => $file->getImportPossible(),
                'comment' => implode(', ', $file->getComments())
            ]);
        };
        return $rows;
    }

    /**
     * Get the data of the protocol file, indexed by the value of the id column
     * @return array[][] id => column key => column value
     */
    private function readProtocol(string $file_id): array
    {
        $array = $this->spreadsheet->dataFromFile($file_id);

        // Skip title (e.g: "Einreichungsprotokoll E-Examen Aufgabe| | | |)
        if (!isset($array[0][1])) {
            array_shift($array);
        }

        // header content => column key
        $header_keys = array_flip(array_map($this->lng->txt(...), self::CSV_MAP));

        // column index => column key
        $keys = array_map(
            fn($value) => $header_keys[$value] ?? 'unknown',
            array_shift($array) ?? []
        );

        $protocol = [];
        foreach ($array as $row) {
            $row = array_combine($keys, $row);
            if (isset($row['id'])) {
                $protocol[$row['id']] = $row;
            }
        }

        return $protocol;
    }

    private function loginName(string $string): string
    {
        return str_replace(' ', '-', $string);
    }
}
