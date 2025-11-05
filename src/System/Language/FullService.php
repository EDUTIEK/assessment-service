<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\System\Language;

interface FullService
{
    /**
     * Add the translations of a language
     * @param string $code 2-letter language code, e.g. 'en'
     * @param array<string, string> $texts key/value array of language variables and texts
     */
    public function addLanguage(string $code, array $texts): self;

    /**
     * Set the preferred language
     * @param string $code 2-letter language code, e.g. 'en'
     */
    public function setLanguage(string $code): self;

    /**
     * Set the default language that is used when a variable is not found in the preferred language
     * @param string $code 2-letter language code, e.g. 'en'
     */
    public function setDefaultLanguage(string $code): self;

    /**
     * Returns a translated text identified by its key
     * either in preferred language, in default language or in its original
     */
    public function txt(string $key): string;
}