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

class ImportTypeNrw implements ImportType
{
    private const FILE_PATTERN = '/^\d+von\d+_(\d+-\d+).pdf$/';

    public function __construct(
        private Language $lng
    ) {
    }

    public function detectByFilenames(array $filenames): bool
    {
        return !empty(preg_grep(self::FILE_PATTERN, $filenames));
    }

    public function assignFiles(array $files): ImportResult
    {
        foreach ($files as $file) {
            $login = preg_match(self::FILE_PATTERN, $file->getFileName(), $matches) ? $matches[1] : null;
            if ($login) {
                $file->setRelevant(true)
                    ->setLogin($login);
            }
        }
        return new ImportResult(true);
    }


    public function columns(): array
    {
        return [
            'file' => new Column('text', $this->lng->txt('essay_import_column_file')),
            'id' => new Column('text', $this->lng->txt('essay_import_column_user')),
            'import_possible' => new Column('boolean', $this->lng->txt('essay_import_column_import_possible')),
            'comment' => new Column('text', $this->lng->txt('essay_import_column_comment')),
        ];
    }

    public function rows(array $files): array
    {
        $rows = [];
        foreach ($files as $file) {
            $rows[] = new Row($file->getTempId(), [
                'file' => $file->getFileName(),
                'id' => $file->getLogin(),
                'import_possible' => $file->getImportPossible(),
                'comment' => implode(', ', $file->getComments())
            ]);
        };
        return $rows;
    }
}
