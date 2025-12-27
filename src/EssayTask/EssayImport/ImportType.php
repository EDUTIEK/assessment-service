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

use ILIAS\UI\Component\Symbol\Icon\Icon;

interface ImportType
{
    /**
     * Detect the type by the filenames given in the ZIP content
     * @param string $filenames
     */
    public function detectByFilenames(array $filenames): bool;

    /**
     * Assign the imported files to login names
     * Do import type specific checks
     * @param ImportFile[] $files
     */
    public function assignFiles(array $files): ImportResult;

    /**
     * Get the table columns for listing the import files
     * @return array<string, Column>
     */
    public function columns(): array;

    /**
     * Get the table rows for listing the import files
     * @param ImportFile[] $files
     * @return Row[]
     */
    public function rows(array $files): array;
}
