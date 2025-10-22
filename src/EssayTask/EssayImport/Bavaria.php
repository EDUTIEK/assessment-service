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

use Closure;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Bavaria implements Type
{
    private const FILE_PATTERN = '/^[[:alnum:]]+-\d+_([[:alpha:]]-\d+)_.*.pdf$/';
    private const PROTOCOL_FILE_NAME = 'Abgabe-Protokoll.xlsx';
        // Values are lang vars.
    private const CSV_MAP = [
        'id' => 'by_csv_id',
        'alloc_time' => 'by_csv_alloc_time',
        'used_time' => 'by_csv_used_time',
        'logged_in' => 'by_csv_logged_in',
        'given_up' => 'by_csv_given_up',
        'pdf_hash' => 'by_csv_pdf_hash',
    ];

    /**
     * @param Closure(string): array $load_table_from_file
     */
    public function __construct(
        private readonly Problems $problems,
        private readonly Import $import,
        private readonly Closure $load_table_from_file,
    ) {
    }

    public function rows(array $files, array $hashes): array
    {
        $protocol = $this->readProtocol();
        $pdfs = $this->import->keysBy(fn($f) => $this->import->extract(self::FILE_PATTERN, $f, 1), $files);
        unset($pdfs['']);

        return array_map(function (array $row) use ($hashes, $pdfs) {
            $login = $this->loginName($row['id']);
            $problems = $this->problems->problems($login, $pdfs, $hashes);
            $hash_ok = $row['pdf_hash'] === ($hashes[$pdfs[$login] ?? false] ?? false);
            return new Row($row['id'], [
                'file' => $pdfs[$login] ?? null,
                'user' => $login,
                'id' => $row['id'],
                'hash_ok' => $hash_ok,
                'import_possible' => $hash_ok && $problems['errors'] == [],
                'comment' => join(', ', array_merge(...array_values($problems))),
            ], $problems['overwrites'], $hash_ok && $problems['errors'] == []);
            }, $protocol);
    }

    public function validFilesByLogin(array $files): array
    {
        $files = array_filter(
            $this->rows($files, $this->import->buildPdfHashes($files)),
            fn($row) => $row->getFields()['hash_ok']//  && $row['error'] === ''
        );

        $field = fn($k) => fn($r) => $r->getFields()[$k];
        return array_combine(array_map($field('user'), $files), array_map($field('file'), $files));
    }

    public function columns(): array
    {
        return [
            'file' => new Column('text', $this->import->txt('essay_import_column_file')),
            'user' => new Column('text', $this->import->txt('essay_import_column_user')),
            'id' => new Column('text', $this->import->txt('essay_import_column_id')),
            'hash_ok' => new Column('boolean', $this->import->txt('essay_import_column_hash_ok')),
            'import_possible' => new Column('boolean', $this->import->txt('essay_import_column_import_possible')),
            'comment' => new Column('text', $this->import->txt('essay_import_column_comment')),
        ];
    }

    public function relevantFiles(array $files): array
    {
        return array_filter($files, fn($file) => $file === self::PROTOCOL_FILE_NAME || preg_match(self::FILE_PATTERN, $file));
    }

    private function readProtocol(): array
    {
        $array = ($this->load_table_from_file)($this->import->getRealPath(self::PROTOCOL_FILE_NAME));
        // Skip title (e.g: "Einreichungsprotokoll E-Examen Aufgabe| | | |)
        if (!isset($array[0][1])) {
            array_shift($array);
        }
        // Numeric to named index by header:
        $rename_index = $this->csvRow($array[0]);
        array_shift($array);

        return array_map($rename_index, $array);
    }

    private function csvRow(array $header): Closure
    {
        $txts = array_flip(array_map($this->import->txt(...), self::CSV_MAP));

        $header = array_map(fn($key) => $txts[$key] ?? 'unknown', $header);
        return fn(array $row) => array_combine($header, $row);
    }

    private function loginName(string $string): string
    {
        return str_replace(' ', '-', $string);
    }
}
