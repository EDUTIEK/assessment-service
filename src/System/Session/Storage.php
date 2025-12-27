<?php

namespace Edutiek\AssessmentService\System\Session;

interface Storage
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): void;
}