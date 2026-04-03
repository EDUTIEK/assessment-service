<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Language;

interface ReadService
{
    /**
     * Retrun all texts defined for a language as a key/value array
     */
    public function all(): array;

    /**
     * Returns a translated text identified by its key
     * either in preferred language, in default language or in its original
     */
    public function txt(string $key, array $variables = []): string;
}
