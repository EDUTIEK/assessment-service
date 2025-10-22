<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

interface Problems
{
        /**
     * @param array<string, string> $pdfs Filenames by login
     * @param array<string, string> $hashes Hashes by filename
     *
     * @return array{errors: string[], overwrites: string[]}
     */
    public function problems(string $login, array $pdfs, array $hashes): array;
}
