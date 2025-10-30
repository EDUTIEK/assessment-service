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

interface Import
{
    /**
     * @param string[] $pdfs
     *
     * @return array<string, string> where array_keys($this->buildPdfHashes($pdfs)) === $pdfs and values are the hashed pdfs.
     */
    public function buildPdfHashes(array $pdfs): array;
    public function permanentId(string $file_id): string;
    public function getRealPath(string $id): ?string;
}
