<?php

namespace Edutiek\AssessmentService\System\Session;

class Service implements Storage
{
    public function __construct(
        private Storage $raw,
        private string $section
    ) {
    }

    public function get(string $key) : mixed
    {
        $data = $this->raw->get($this->section);
        return $data[$key] ?? null;
    }

    public function set(string $key, mixed $value) : void
    {
        $data = $this->raw->get($this->section) ?? [];
        $data[$key] = $value;
        $this->raw->set($this->section, $data);
    }
}