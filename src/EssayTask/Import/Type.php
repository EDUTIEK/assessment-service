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

interface Type
{
    /**
     * @param string[] $files
     * @param array<string, string> $hashes Hash values for $files: isset($hashes[$files[0]]) === true
     *
     * @return Row[]
     */
    public function rows(array $files, array $hashes): array;

    /**
     * @param string[] $files
     * @return array<string, string>
     */
    public function validFilesByLogin(array $files): array;

    /**
     * @return array<string, Column>
     */
    public function columns(): array;

    /**
     * Returns a subset of $files, which should be all files that may be relevant for this import type.
     *
     * @param string[] $files
     * @return string[]
     */
    public function relevantFiles(array $files): array;
}
