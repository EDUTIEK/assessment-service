<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

interface Info
{
    /**
     * @param array<string, string> $pdfs Filenames by login
     * @param array<string, string> $hashes Hashes by filename
     *
     * @return array{errors: string[], overwrites: string[]}
     */
    public function problems(string $login, array $pdfs, array $hashes): array;

    /**
     * @template A
     * @template B
     *
     * @param callable(A): B $proc
     * @param A[] $array
     * @return array<B, A>
     */
    public function keysBy(callable $proc, array $array): array;
    public function extract(string $pattern, string $subject, int $sub_match): ?string;
    public function txt(string $lang_var): string;
}
