<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Language;

use Edutiek\AssessmentService\System\Language\FullService;

class Service implements FullService
{
    private array $texts = [];
    private string $language = '';
    private string $default_language = '';

    public function addLanguage(string $code, array $texts): self
    {
        $this->texts[$code] = $texts;
        return $this;
    }

    public function setLanguage(string $code): self
    {
        $this->language = $code;
        return $this;
    }

    public function setDefaultLanguage(string $code): self
    {
        $this->default_language = $code;
        return $this;
    }

    /**
     * Retrieves a translated text string based on a given key and replaces variables within the text.
     * Variables are referenced in the text embedded in '{' and '}'
     *
     * @param string $key The key identifying the text to retrieve.
     * @param array $variables An associative array of placeholder variables and their replacement values.
     *
     * @return string The processed text with variables replaced.
     */
    public function txt(string $key, array $variables = []): string
    {
        $text = $this->texts[$this->language][$key] ?? $this->texts[$this->default_language][$key] ?? $key;
        foreach ($variables as $variable => $value) {
            $test = str_replace('{' . $variable . '}', $value, $text);
        }
        return $test;
    }
}
