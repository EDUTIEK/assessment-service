<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\System\Language;

use Edutiek\AssessmentService\System\Language\FullService;

class Service implements FullService
{
    private array $texts = [];
    private string $language = 'en';
    private string $default_language = 'en';

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

    public function txt(string $key) : string
    {
        return $this->texts[$this->language][$key] ?? $this->texts[$this->default_language][$key] ?? $key;
    }
}